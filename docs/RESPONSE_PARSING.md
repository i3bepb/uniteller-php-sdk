# Обработка ответов Uniteller

`ParserInterface::parse(string $response): array` описывает только декодирование формата. CSV, XML и JSON не создают доменные объекты и не интерпретируют коды операций.

`RequestManager` создаёт и отправляет HTTP-запрос, логирует обмен, проверяет HTTP-статус и декодирует body. `executeRequest(BuilderInterface $builder)` возвращает `DecodedResponse`: массив данных вместе с исходными PSR request/response. Массив данных доступен через `DecodedResponse::getData()`. Интерпретация бизнес-ошибок выполняется после декодирования.

Цепочки builders:

- `ResultsBuilder`, `FiscalResultsBuilder`, `ConfirmBuilder`, `RecurrentBuilder`: HTTP → `RequestManager` → `ParserCsv` / `ParserXml` → `LegacyCsvResponseParser` / `LegacyXmlResponseParser` → `Order[]`. Фабрика выбирает преобразователь по формату; различия названий полей и значений по умолчанию сохранены. Ошибки legacy-ответов обрабатываются через `ExceptionFactory` с исходным HTTP-контекстом.
- `FiscalConfirmBuilder`: HTTP → `RequestManager` → `ParserXml` → `FiscalConfirmResultParser` → `FiscalConfirmResult`. `Result` обязателен. Ненулевой, в том числе неизвестный, целочисленный код сохраняется как бизнес-результат. Присутствующий `Receipt` декодируется через `ParserReceiptFromBase64` в `FiscalReceipt[]`, в том числе при неуспешном результате.
- `CancelBuilder`: HTTP → `RequestManager` → `ParserJson` → `CancelResultParser` → прежний массив с полями ответа и декодированными чеками. Существующая поддержка JSON не расширена на другие endpoint-ы.

HTTP 4xx/5xx, исключения PSR HTTP client, повреждённый XML/JSON/Base64 остаются исключениями. Отсутствующий или некорректный `Result` вызывает `InvalidResponseException`. Пустой XML также считается повреждённым ответом.

Контейнер создаёт новый `RequestManager` для каждого получения по определению класса: иначе ранее выбранный CSV-парсер мог использоваться после переключения builder на XML. Явная регистрация готового объекта менеджера по-прежнему допустима.

## Изменения низкоуровневого API

Builders с результатом `Order[]` сохраняют этот контракт; `FiscalConfirmBuilder::process(): FiscalConfirmResult` теперь возвращает объявленный объект. Из форматных парсеров удалены конструктор с receipt-парсером, `parseOrders()`, `parseErrors()` и `parseResults()`. Методы `RequestManager::executeRequestAndParseResponseOrders()` и `executeRequestAndParseResponseReceipt()` заменены на `executeRequest()` с последующей интерпретацией ответа на уровне builder. Внешним потребителям этих низкоуровневых методов потребуется миграция.

Из legacy XML-преобразования удалён вызов отсутствующего `Order::setSignature()`, из-за которого непустые XML-ответы с заказами завершались ошибкой.

## Сверка с документацией и оставшиеся расхождения

Основной источник FiscalConfirm: [02.1.18 rev. 5, §3.10](тп_-_платежи_с_фискализацией_-_v._02.1.18_rev._5.md#page-52), таблицы 6–7, и [приложение 8](тп_-_платежи_с_фискализацией_-_v._02.1.18_rev._5.md#page-93). Старые версии для этого рефакторинга не использовались.

- В таблице 7 `Receipt` отмечен обязательным. По требованиям задачи SDK допускает его отсутствие и возвращает пустой массив чеков; при наличии поля повреждённые данные не игнорируются. Это относится и к `Result=0`.
- В описании процедуры подтверждения той же версии документа употребляется `ErrorCode` и имя `Subtotal_P`, тогда как таблицы запроса/ответа §3.10 содержат `Subtotal` и `Result`. Для разбора ответа приоритет отдан таблице 7: `ErrorCode` не заменяет отсутствующий `Result`.
- Код 4 отсутствует в приложении 8, но указан в описании процедуры подтверждения как ошибка суммы. Значения `FiscalConfirmResultCode` не изменены; код 11 подтверждён приложением 8.
- В актуальном [документе интернет-эквайринга, §8.3.1.5](тп_интернет-эквайринг_-_v1.43_rev._48.md#page-87) XML содержит атрибуты `firstcode`/`secondcode`. Прежняя обработка SDK их не интерпретировала; в этом рефакторинге это поведение сохранено. Поддержка ошибок из этих атрибутов требует отдельной задачи.
- `gdsPaymentPurposeId` исправлен: читается из `gds_payment_purpose_id` независимо от `firstname`. Имя поля подтверждено таблицей полей результатов авторизации на [стр. 82 актуального документа интернет-эквайринга](тп_интернет-эквайринг_-_v1.43_rev._48.md#page-82). В сокращённом примере XML §8.3.1.5 это поле не перечислено. Значение 123 в регрессионном тесте проверяет перенос значения; в документации описаны значения 10/20.

`ConfirmBuilder` и `ResultsBuilder` теперь передают в `FormatParameter` единый список строковых имён: `array_keys(Format::getSupportedForEndpoint($endpoint))`. `FiscalResultsBuilder` наследует эту настройку. `setFormat(Format::CSV)` и `setFormat(Format::XML)` сохраняют публичный контракт; `getResponseFormat()` возвращает строковое имя, а сериализация через `Format::resolve()` — числовой код для endpoint. Валидация проверяет и список `allowed`, и поддержку endpoint, чтобы ошибочно расширенный список не откладывал отказ до сериализации. Числовые коды как вход `setFormat()` не принимаются. У `ResultsBuilder` требование явно установить формат сохранено.

Другие подозрительные legacy XML mappings оставлены без изменений:

- `billnumber` приводится к `int` перед строковым setter: ведущие нули теряются.
- `sum` приводится к `float` перед строковым setter: исходная десятичная запись и точность могут измениться.

## Кандидаты на следующий рефакторинг

WDDX, SOAP и BRACKETS присутствуют в `Format` и проверках допустимых значений, но реализованных декодеров для них нет: `getParserByFormat()` выбрасывает `ParserNotImplementedException`. Встроенные builders не выбирают их по умолчанию. Эти записи сохранены. CSV и XML используются активно; JSON нужен для отмены с фискализацией. Таблица форматов для `ApiEndpoints::CARD` также не имеет соответствующего builder в текущем `src/`.

## Проверки исправлений FormatParameter и GDS

- 108 тестов форматов, GDS и регрессий обработки ответов: 372 assertions, успешно (PHPUnit 8.5.42, PHP 7.4).
- Синтаксис всех 232 PHP-файлов `src/` и `tests/` проверен PHP 7.2, ошибок нет.
- Полный suite повторно запущен: обнаружены 1232 теста; выполнение снова обрывается на отсутствующем `Concern\HasAddress` в старых тестах. Общий suite не проходит.
- Другие legacy XML mappings, архитектура response parsing, FiscalConfirm и константы `Format` в этом исправлении не менялись.

Файлы этого исправления:

- [`src/Parameter/FormatParameter.php`](../src/Parameter/FormatParameter.php)
- [`src/Confirm/ConfirmBuilder.php`](../src/Confirm/ConfirmBuilder.php)
- [`src/Results/ResultsBuilder.php`](../src/Results/ResultsBuilder.php)
- [`src/Response/LegacyXmlResponseParser.php`](../src/Response/LegacyXmlResponseParser.php)
- [`tests/Parameter/FormatParameterTest.php`](../tests/Parameter/FormatParameterTest.php) — новый.
- [`tests/Response/BuilderFormatsTest.php`](../tests/Response/BuilderFormatsTest.php) — новый.
- [`tests/Response/LegacyXmlResponseParserTest.php`](../tests/Response/LegacyXmlResponseParserTest.php)
- [`docs/RESPONSE_PARSING.md`](RESPONSE_PARSING.md)

## Проверки исходного рефакторинга

- PHPUnit 8.5.42 на PHP 7.4: 85 тестов затронутых обработчиков и методов `process()`, 300 assertions — успешно.
- PHP 7.2: синтаксическая проверка всех 230 PHP-файлов в `src/` и `tests/` — успешно. Установленный `vendor` требует PHP >=7.4, поэтому полный PHPUnit на PHP 7.2 блокируется Composer platform check; зависимости в рамках задачи не менялись.
- Обычный полный suite запущен до и после изменений. В обоих случаях он обрывается на отсутствующем `Tmconsulting\Uniteller\Concern\HasAddress` в старых тестах.
- Дополнительно полный suite выполнен с `--process-isolation`: 1209 тестов, 867 assertions, 673 errors, 38 failures, 165 warnings. Общий suite не проходит. Среди проблем — старые отсутствующие traits, `SignatureInterface`, устаревшие классы builders и проверки прежних сообщений валидации. Это не заменяет отдельную миграцию устаревшего набора тестов.
- `git diff --check` — успешно.

## Файлы исходного рефакторинга

Изменены:

- [`src/Cancel/CancelBuilder.php`](../src/Cancel/CancelBuilder.php)
- [`src/Confirm/ConfirmBuilder.php`](../src/Confirm/ConfirmBuilder.php)
- [`src/Confirm/FiscalConfirmBuilder.php`](../src/Confirm/FiscalConfirmBuilder.php)
- [`src/Confirm/FiscalConfirmResult.php`](../src/Confirm/FiscalConfirmResult.php)
- [`src/Dependency/Container.php`](../src/Dependency/Container.php)
- [`src/Recurrent/RecurrentBuilder.php`](../src/Recurrent/RecurrentBuilder.php)
- [`src/Request/ParserCsv.php`](../src/Request/ParserCsv.php)
- [`src/Request/ParserInterface.php`](../src/Request/ParserInterface.php)
- [`src/Request/ParserJson.php`](../src/Request/ParserJson.php)
- [`src/Request/ParserXml.php`](../src/Request/ParserXml.php)
- [`src/Request/RequestManager.php`](../src/Request/RequestManager.php)
- [`src/Results/ResultsBuilder.php`](../src/Results/ResultsBuilder.php)
- [`tests/Cancel/CancelBuilderTest.php`](../tests/Cancel/CancelBuilderTest.php)
- [`tests/Confirm/ConfirmBuilderTest.php`](../tests/Confirm/ConfirmBuilderTest.php)
- [`tests/Confirm/ReceiptedConfirmBuilderTest.php`](../tests/Confirm/ReceiptedConfirmBuilderTest.php)
- [`tests/Recurrent/RecurrentBuilderTest.php`](../tests/Recurrent/RecurrentBuilderTest.php)
- [`tests/Request/ParserCsvTest.php`](../tests/Request/ParserCsvTest.php)
- [`tests/Request/ParserJsonTest.php`](../tests/Request/ParserJsonTest.php)
- [`tests/Request/ParserXmlTest.php`](../tests/Request/ParserXmlTest.php)
- [`tests/Request/RequestManagerTest.php`](../tests/Request/RequestManagerTest.php)
- [`tests/Results/ResultsBuilderTest.php`](../tests/Results/ResultsBuilderTest.php)

Добавлены в рабочее дерево (включая три существовавшие заготовки):

- [`src/Cancel/CancelResultParser.php`](../src/Cancel/CancelResultParser.php)
- [`src/Confirm/FiscalConfirmResultParser.php`](../src/Confirm/FiscalConfirmResultParser.php)
- [`src/Exception/InvalidResponseException.php`](../src/Exception/InvalidResponseException.php)
- [`src/Request/DecodedResponse.php`](../src/Request/DecodedResponse.php)
- [`src/Response/LegacyCsvResponseParser.php`](../src/Response/LegacyCsvResponseParser.php)
- [`src/Response/LegacyResponseParserFactory.php`](../src/Response/LegacyResponseParserFactory.php)
- [`src/Response/LegacyResponseParserInterface.php`](../src/Response/LegacyResponseParserInterface.php)
- [`src/Response/LegacyXmlResponseParser.php`](../src/Response/LegacyXmlResponseParser.php)
- [`tests/Confirm/FiscalConfirmResponseTest.php`](../tests/Confirm/FiscalConfirmResponseTest.php)
- [`tests/Response/BuilderResponsesTest.php`](../tests/Response/BuilderResponsesTest.php)
- [`tests/Response/LegacyCsvResponseParserTest.php`](../tests/Response/LegacyCsvResponseParserTest.php)
- [`tests/Response/LegacyXmlResponseParserTest.php`](../tests/Response/LegacyXmlResponseParserTest.php)
- [`tests/ResponseTestCase.php`](../tests/ResponseTestCase.php)
- [`docs/RESPONSE_PARSING.md`](RESPONSE_PARSING.md)
