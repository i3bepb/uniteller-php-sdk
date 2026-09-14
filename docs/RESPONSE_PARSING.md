# Обработка ответов Uniteller

`ParserInterface::parse(string $response): array` описывает только декодирование формата. CSV, XML и JSON не создают доменные объекты и не интерпретируют коды операций.

`RequestManager` создаёт и отправляет HTTP-запрос, логирует обмен, проверяет HTTP-статус и декодирует body. `executeRequest()` и `requestDecoded()` возвращают `DecodedResponse`: массив данных вместе с исходными PSR request/response. Метод `request()` возвращает только массив данных. Интерпретация бизнес-ошибок выполняется после него.

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
- В legacy XML-преобразовании сохранилось старое чтение `gdsPaymentPurposeId` из `firstname`, хотя документация описывает отдельное поле `gds_payment_purpose_id`. Это отдельное исправление семантики полей.

Дополнительная обнаруженная проблема вне обработки ответов: `ConfirmBuilder` передаёт в `FormatParameter` числовые значения допустимых форматов, а преобразование параметра ожидает строковое имя. Явный `setFormat()` у него работает некорректно; текущий путь без этого параметра использует CSV. У `ResultsBuilder` валидация требует явного формата; использование `setFormat(Format::CSV)` сохранено.

## Кандидаты на следующий рефакторинг

WDDX, SOAP и BRACKETS присутствуют в `Format` и проверках допустимых значений, но реализованных декодеров для них нет: `getParserByFormat()` выбрасывает `ParserNotImplementedException`. Встроенные builders не выбирают их по умолчанию. Эти записи сохранены. CSV и XML используются активно; JSON нужен для отмены с фискализацией. Таблица форматов для `ApiEndpoints::CARD` также не имеет соответствующего builder в текущем `src/`.

## Проверки

- PHPUnit 8.5.42 на PHP 7.4: 85 тестов затронутых обработчиков и методов `process()`, 300 assertions — успешно.
- PHP 7.2: синтаксическая проверка всех 230 PHP-файлов в `src/` и `tests/` — успешно. Установленный `vendor` требует PHP >=7.4, поэтому полный PHPUnit на PHP 7.2 блокируется Composer platform check; зависимости в рамках задачи не менялись.
- Обычный полный suite запущен до и после изменений. В обоих случаях он обрывается на отсутствующем `Tmconsulting\Uniteller\Concern\HasAddress` в старых тестах.
- Дополнительно полный suite выполнен с `--process-isolation`: 1209 тестов, 867 assertions, 673 errors, 38 failures, 165 warnings. Общий suite не проходит. Среди проблем — старые отсутствующие traits, `SignatureInterface`, устаревшие классы builders и проверки прежних сообщений валидации. Это не заменяет отдельную миграцию устаревшего набора тестов.
- `git diff --check` — успешно.

## Файлы рефакторинга

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
