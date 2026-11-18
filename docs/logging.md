# Политика логирования

SDK принимает PSR-3 логгер через `withLogger()`. Без него используется `NullLogger`.
Сообщение каждой записи — постоянный идентификатор события `uniteller.<компонент>.<событие>`.
Весь контекст находится под единственным корневым ключом `uniteller`:

```php
$logger->info('uniteller.http.request_sending', [
    'uniteller' => [
        'request_id' => $requestId,
        'url' => $url,
        'method' => 'POST',
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'parameters' => $parameters,
    ],
]);
```

Каталог событий:

| Событие | Уровень | Момент записи |
| --- | --- | --- |
| `uniteller.payment_form.url_generated` | debug | Генерация URL платёжной формы |
| `uniteller.http.request_sending` | info | Перед вызовом HTTP-клиента, включая попытки с транспортной ошибкой |
| `uniteller.http.response_received` | info | Получен HTTP-ответ, до проверки его статуса и разбора |
| `uniteller.callback.received` | info | Получен callback, до проверки параметров и подписей |
| `uniteller.callback.receipt_parsing_failed` | error | Ошибка разбора чека callback |
| `uniteller.callback.rejected` | error | Callback отклонён; `reason` и `http_status` содержат причину и код |
| `uniteller.cancel.request_prepared`, `uniteller.cancel.failed` | debug / error | Подготовка / ошибка отмены |
| `uniteller.confirm.request_prepared`, `uniteller.confirm.failed` | debug / error | Подготовка / ошибка подтверждения |
| `uniteller.fiscal_confirm.request_prepared`, `uniteller.fiscal_confirm.failed` | debug / error | Подготовка / ошибка фискального подтверждения |
| `uniteller.fiscal_confirm.receipt_prepared` | debug | Чек фискального подтверждения |
| `uniteller.recurrent.request_prepared`, `uniteller.recurrent.failed` | debug / error | Подготовка / ошибка рекуррентного платежа |
| `uniteller.results.request_prepared`, `uniteller.results.failed` | debug / error | Подготовка / ошибка запроса результатов |
| `uniteller.fiscal_results.request_prepared`, `uniteller.fiscal_results.failed` | debug / error | Те же места унаследованной реализации, фискальный endpoint |
| `uniteller.signature.verification_completed` | debug | Проверка подписи, включая `valid=false`; `algorithm=md5` |
| `uniteller.signature.calculated` | debug | Расчёт подписи; `algorithm=md5` или `sha256` |

Количество записей и условия debug сохранены. Событие `callback.verified` не добавлялось.
При ошибке разбора чека callback сохраняются обе записи: `receipt_parsing_failed` и `rejected`.
URL вынесены из сообщений в `uniteller.url` и проходят маскирование.
Для потребителей логов это изменение формата: фильтры сообщений нужно обновить,
пути полей теперь имеют вид `uniteller.request_id`, `uniteller.parameters`, `uniteller.error`.

Параметры исходящих HTTP-запросов (`parameters`) и тела ответов (`body`) записываются
на уровне `info` постоянно, независимо от `debug`. Тело исходящего запроса отдельно
не логируется: оно формируется из тех же параметров через `http_build_query()`.

```php
use Tmconsulting\Uniteller\Support\LoggingPolicy;
use Tmconsulting\Uniteller\Uniteller;

$policy = (new LoggingPolicy())->maskPersonalData(true);
$uniteller = (new Uniteller())
    ->withLogger($logger)
    ->withLoggingPolicy($policy);
```

Настраивайте SDK до создания билдеров и обработки callback. По умолчанию маскирование
персональных данных включено. Вызов `maskPersonalData(false)` сохраняет в логах известные
SDK поля телефона (включая `PhoneVerified`), email, ФИО, адреса, покупателя и `IData`,
а также полное содержимое чеков.
Это единственная настройка политики.

Поля `Password` и `bookingcom_pincode`, а также их промежуточные хеши скрываются
во всех записях, кроме отладки расчёта подписи, описанной ниже.
В поле `headers` логов запросов и ответов записывается только `Content-Type` в виде строки.
Остальные заголовки не передаются в это поле лога; исходные HTTP-сообщения сохраняют все заголовки.

`LogSanitizer` очищает вложенные массивы, тела `body`/`raw_post` в форматах JSON, XML,
CSV с заголовком колонок, form-urlencoded, а также query-параметры и известные поля URI.
Произвольные строковые поля не разбираются как JSON/XML по начальному символу.
XML обрабатывается без загрузки внешних ресурсов;
документы с DTD в лог не попадают. Имена полей сопоставляются точно, включая регистр,
подчёркивания и дефисы, без нормализации. Неподдерживаемое или повреждённое тело заменяется
маркером `[unparsed body: N bytes]`, без возврата к записи исходного содержимого.
Произвольные поля свободного текста не являются гарантированно очищенными персональными
данными: маскирование основано на известных именах полей и структуре формата.

Списки чувствительных полей используют документированные имена из
`UnitellerParameterName` и `SFields`. Предположительные синонимы вроде `CustomerEmail`
и совпадения по части имени вроде `PasswordHint` не используются.

Билдеры передают в `Signature` именованные параметры. Метод `debugSignatureCalculation()`
пишет исходные значения, все промежуточные хеши и итоговую подпись без маскирования,
напрямую в логгер приложения. Запись выполняется только при включённом `debug`;
`LoggingPolicy` на неё не действует. В контексте доступны в том числе пароль,
персональные данные и полный чек. Этот режим предназначен для временной отладки
расчёта подписи. Порядок параметров и вычисление подписи не меняются.

Политика применяется перед передачей записи логгеру приложения, включая входящие callback
до проверки подписи и debug-запись `Signature::verify()`. Исключение составляет
описанная выше отладка расчёта подписи. Данные передаются через структурированный
контекст; исходные параметры, отправляемые тела, ответы и вычисления подписи не меняются.
В месте логирования `ExceptionContext::fromThrowable()` преобразует исключение в массив
с полями `class`, `code`, `file`, `line`, который передаётся в контексте под ключом `error`.
Исходный объект, стек вызовов и предыдущие исключения не передаются логгеру.
`ExceptionContext::logMessage()` формирует пояснение в `uniteller.message`, добавляя только проверенный
фиксированный текст собственного исключения SDK к постоянному пояснению операции. Идентификатор события не меняется. Класс и сообщение сопоставляются точно
со списком разрешённых значений. Для внешних исключений, ошибок PHP, пользовательских
подклассов и любых других сообщений сохраняется постоянный текст без `getMessage()` в логе.
`LogSanitizer` маскирует уже подготовленные данные и не раскрывает объекты исключений.
Политика действует на записи SDK, а не на собственное логирование HTTP-клиента или приложения.

При `maskPersonalData(true)` поля `Receipt`/`Receipts` заменяются целиком на
`LogSanitizer::MASK` (`[REDACTED]`), включая объекты, массивы, JSON и Base64.
Известное поле контекста `uniteller.receipt` платёжной формы маскируется по тому же правилу;
это не меняет точное сопоставление регистра имён полей API.
При `maskPersonalData(false)` значение чека сохраняется как есть: Base64 не декодируется,
поля чека не разбираются. Правило действует в запросах, ответах, callback,
query-параметрах и debug-записях, кроме `debugSignatureCalculation()`.
Исходные чеки передаются и обрабатываются без изменений.

Методы `LogSanitizer`, принимающие `LoggingPolicy`, требуют явно переданный объект
политики. Значение `null` и создание политики по умолчанию внутри санитайзера не поддерживаются.
