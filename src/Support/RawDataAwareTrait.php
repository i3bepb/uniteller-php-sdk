<?php

namespace Tmconsulting\Uniteller\Support;

trait RawDataAwareTrait
{
    /**
     * Исходные данные из ответа API. Т.е. ассоциативный массив, который получился из json.
     *
     * @var array
     */
    private $rawData = [];

    /**
     * Известные SDK поля.
     *
     * Ключ — нормализованный путь поля.
     * Значение — цепочка getter'ов от текущего объекта.
     *
     * Например:
     *
     * fiscal.register.fiscalnumber => [
     *     'getFiscal',
     *     'getRegister',
     *     'getFiscalNumber',
     * ]
     *
     * @var array
     */
    private $knownFields = [];

    /**
     * Поля, неизвестные текущей версии SDK.
     *
     * [
     *     normalizedPath => [
     *         'path'  => originalPath,
     *         'value' => rawValue,
     *     ],
     * ]
     *
     * @var array
     */
    private $extraFields = [];

    /**
     * @param array $rawData Исходные данные, т.е. ассоциативный массив, который получился из json.
     *
     * @throws \ReflectionException
     */
    protected function setRawData(array $rawData)
    {
        $this->rawData = $rawData;
        $this->knownFields = [];
        $knownRawPaths = [];
        $this->collectKnownFields(get_class($this), $this, '', [], $this->knownFields, $knownRawPaths, true);

        $this->extraFields = [];
        $this->collectExtraFields($this->rawData, '', $knownRawPaths, $this->extraFields);
    }

    /**
     * Получить исходные данные API.
     *
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * Получить значение поля.
     *
     * Известное SDK поле всегда читается из DTO через getter.
     * Неизвестное поле читается из исходных данных API.
     *
     * @param string $path
     * @param mixed $default
     *
     * @return mixed
     */
    public function getField(string $path, $default = null)
    {
        $found = false;
        $value = $this->resolveField($path, $found);
        return $found ? $value : $default;
    }

    /**
     * Проверить наличие поля.
     *
     * @param string $path
     *
     * @return bool
     */
    public function hasField(string $path): bool
    {
        $found = false;
        $this->resolveField($path, $found);
        return $found;
    }

    /**
     * Получить пути полей, неизвестных текущей версии SDK.
     * Например:
     * [
     *     'new_field',
     *     'fiscal.register.new_field',
     *     'lines.*.new_field',
     * ]
     *
     * @return array
     */
    public function getExtraFields(): array
    {
        $result = [];
        foreach ($this->extraFields as $field) {
            $result[] = $field['path'];
        }
        return $result;
    }

    /**
     * Разрешить путь к полю.
     *
     * @param string $path
     * @param bool $found
     *
     * @return mixed
     */
    private function resolveField(string $path, bool &$found)
    {
        $normalizedPath = $this->normalizeRawFieldPath($path);
        if ($normalizedPath === '' || strpos($normalizedPath, '*') !== false) {
            $found = false;
            return null;
        }
        /*
         * Известное SDK поле.
         */
        if (isset($this->knownFields[$normalizedPath])) {
            return $this->getKnownFieldValue($this->knownFields[$normalizedPath], $found);
        }
        /*
         * Вложенное значение произвольного известного массива.
         *
         * Например:
         * optional.foo.bar
         * userRequisite.title
         */
        $value = $this->resolveKnownArrayPath($normalizedPath, $found);
        if ($found) {
            return $value;
        }
        /*
         * Неизвестное SDK поле.
         */
        return $this->resolveExtraPath($normalizedPath, $found);
    }

    /**
     * Выполнить заранее подготовленную цепочку getter'ов.
     * Если null получен последним getter'ом — поле найдено и его значение действительно null.
     * Если null получен посередине пути — вложенный путь в текущем DTO отсутствует.
     *
     * Например:
     * getField('senderEmail') при null => found = true
     * getField('customer.email') при customer = null => found = false
     *
     * @param array $getters
     * @param bool $found
     *
     * @return mixed
     */
    private function getKnownFieldValue(array $getters, bool &$found)
    {
        $value = $this;
        $lastIndex = count($getters) - 1;
        foreach ($getters as $index => $getter) {
            if (!is_object($value)) {
                $found = false;
                return null;
            }
            $value = $value->{$getter}();
            if ($value === null && $index < $lastIndex) {
                $found = false;
                return null;
            }
        }
        $found = true;
        return $value;
    }

    /**
     * Попытаться пройти внутрь известного SDK массива произвольной структуры.
     *
     * Например: optional.foo.bar
     *
     * Внутрь списков не идём: lines.0.name не поддерживается.
     *
     * @param string $normalizedPath
     * @param bool $found
     *
     * @return mixed
     */
    private function resolveKnownArrayPath(string $normalizedPath, bool &$found)
    {
        $segments = explode('.', $normalizedPath);

        /*
         * Ищем самый длинный известный родительский путь.
         */
        for ($i = count($segments) - 1; $i > 0; $i--) {
            $prefix = implode('.', array_slice($segments, 0, $i));
            if (!isset($this->knownFields[$prefix])) {
                continue;
            }
            $prefixFound = false;
            $value = $this->getKnownFieldValue($this->knownFields[$prefix], $prefixFound);
            if (!$prefixFound || !is_array($value)) {
                continue;
            }
            /*
             * Коллекция является границей.
             */
            if ($this->isList($value)) {
                continue;
            }
            return $this->resolveArrayPath($value, array_slice($segments, $i), $found);
        }
        $found = false;
        return null;
    }

    /**
     * Получить неизвестное SDK поле.
     * Поддерживает проход внутри полностью неизвестного блока.
     *
     * Например:
     * new_block => [
     *     'foo' => [
     *         'bar' => 123,
     *     ],
     * ]
     * getField('new_block.foo.bar') => 123
     *
     * @param string $normalizedPath
     * @param bool $found
     *
     * @return mixed
     */
    private function resolveExtraPath(string $normalizedPath, bool &$found)
    {
        if (isset($this->extraFields[$normalizedPath])) {
            $found = true;
            return $this->extraFields[$normalizedPath]['value'];
        }
        $segments = explode('.', $normalizedPath);
        /*
         * Ищем ближайший неизвестный родительский блок.
         */
        for ($i = count($segments) - 1; $i > 0; $i--) {
            $prefix = implode('.', array_slice($segments, 0, $i));
            if (!isset($this->extraFields[$prefix])) {
                continue;
            }
            return $this->resolveArrayPath($this->extraFields[$prefix]['value'], array_slice($segments, $i), $found);
        }
        $found = false;

        return null;
    }

    /**
     * Пройти по ассоциативному массиву.
     *
     * @param mixed $value
     * @param array $segments
     * @param bool $found
     *
     * @return mixed
     */
    private function resolveArrayPath($value, array $segments, bool &$found)
    {
        foreach ($segments as $segment) {
            if (!is_array($value)) {
                $found = false;
                return null;
            }
            /*
             * Числовой список является границей.
             */
            if ($this->isList($value)) {
                $found = false;
                return null;
            }
            $keyFound = false;
            $key = $this->findArrayKey($value, $segment, $keyFound);
            if (!$keyFound) {
                $found = false;
                return null;
            }
            $value = $value[$key];
        }
        $found = true;
        return $value;
    }

    /**
     * Построить пути до известных полей DTO.
     *
     * @param string $className Текущий класс DTO.
     * @param object|null $object Реальный экземпляр DTO, если существует.
     * @param string $prefix Текущий API-путь.
     * @param array $getters Getter-chain от корневого DTO.
     * @param array $knownFields Пути для getField().
     * @param array $knownRawPaths Все известные API-пути.
     * @param bool $exposeFieldRoutes Можно ли добавлять пути в knownFields.
     *
     * @throws \ReflectionException
     * @throws \LogicException
     */
    private function collectKnownFields(
        string $className,
        $object,
        string $prefix,
        array $getters,
        array &$knownFields,
        array &$knownRawPaths,
        bool $exposeFieldRoutes
    ) {
        foreach ($this->getClassFields($className) as $field) {
            $propertyName = $field['name'];
            $path = $prefix === '' ? $propertyName : $prefix . '.' . $propertyName;
            $normalizedPath = $this->normalizeRawFieldPath($path);
            $knownRawPaths[$normalizedPath] = true;
            $fieldGetters = $getters;
            $fieldGetters[] = $field['getter'];
            if ($exposeFieldRoutes) {
                $knownFields[$normalizedPath] = $fieldGetters;
            }

            /*
             * Getter имеет return type: object, например
             *
             * getCustomer(): ?Customer
             * getParams(): ?Params
             * getFiscal(): Fiscal
             */
            if ($field['kind'] === 'object') {
                $nestedObject = null;
                if ($object !== null) {
                    $value = $field['property']->getValue($object);
                    if (is_object($value)) {
                        $nestedObject = $value;
                    }
                }
                $this->collectKnownFields(
                    $field['class'],
                    $nestedObject,
                    $path,
                    $fieldGetters,
                    $knownFields,
                    $knownRawPaths,
                    $exposeFieldRoutes
                );
                continue;
            }

            /*
             * Для массива native return type говорит только array.
             * Поэтому для Item[], PaymentInfo[] и т.п. смотрим реальные элементы массива.
             */
            if ($field['kind'] === 'array') {
                if ($object === null) {
                    continue;
                }
                $value = $field['property']->getValue($object);
                if (!is_array($value)) {
                    continue;
                }
                $this->collectArrayItemFields($value, $path, $fieldGetters, $knownFields, $knownRawPaths);
                continue;
            }

            /*
             * Если return type у getter отсутствует, то для определения возможной вложенной структуры смотрим
             * реальное значение свойства. Такой случай нужен только для старого/не полностью типизированного DTO.
             */
            if ($field['kind'] === 'unknown' && $object !== null) {
                $value = $field['property']->getValue($object);
                if (is_object($value)) {
                    $this->collectKnownFields(
                        get_class($value),
                        $value,
                        $path,
                        $fieldGetters,
                        $knownFields,
                        $knownRawPaths,
                        $exposeFieldRoutes
                    );
                    continue;
                }
                if (is_array($value)) {
                    $this->collectArrayItemFields(
                        $value,
                        $path,
                        $fieldGetters,
                        $knownFields,
                        $knownRawPaths
                    );
                }
            }
        }
    }

    /**
     * Собрать поля DTO внутри коллекции/массива.
     * Например:
     * lines.*.name
     *
     * @param array $items
     * @param string $path
     * @param array $getters
     * @param array $knownFields
     * @param array $knownRawPaths
     *
     * @throws \ReflectionException
     */
    private function collectArrayItemFields(
        array $items,
        string $path,
        array $getters,
        array &$knownFields,
        array &$knownRawPaths
    ) {
        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }
            $itemPath = $path . '.*';
            $knownRawPaths[$this->normalizeRawFieldPath($itemPath)] = true;
            $this->collectKnownFields(
                get_class($item),
                $item,
                $itemPath,
                $getters,
                $knownFields,
                $knownRawPaths,
                false
            );
        }
    }

    /**
     * Получить описание полей класса.
     * Для каждого свойства обязан существовать публичный getter.
     *
     * @param string $className
     *
     * @return array
     *
     * @throws \ReflectionException
     * @throws \LogicException
     */
    private function getClassFields(string $className): array
    {
        static $cache = [];
        if (isset($cache[$className])) {
            return $cache[$className];
        }
        $reflection = new \ReflectionClass($className);

        $getters = [];
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();
            $getterFieldName = substr($methodName, 3);
            $getters[$this->normalizeRawFieldPath($getterFieldName)] = $method;
        }

        $fields = [];
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if ($propertyName === 'rawData' || $propertyName === 'knownFields' || $propertyName === 'extraFields') {
                continue;
            }
            $normalizedName = $this->normalizeRawFieldPath($propertyName);
            if (!isset($getters[$normalizedName])) {
                throw new \LogicException("Getter for property {$className}::{$propertyName} is not defined.");
            }
            $getter = $getters[$normalizedName];
            $property->setAccessible(true);
            $returnMetadata = $this->getGetterReturnMetadata($getter);
            $fields[$normalizedName] = [
                'name'     => $propertyName,
                'getter'   => $getter->getName(),
                'property' => $property,
                'kind'     => $returnMetadata['kind'],
                'class'    => $returnMetadata['class'],
            ];
        }
        $cache[$className] = $fields;

        return $fields;
    }

    /**
     * Определить тип значения getter'а.
     * Возможные kind:
     *    scalar  — int/string/bool/float и т.п.
     *    object  — DTO class
     *    array   — массив
     *    unknown — return type отсутствует
     *
     * @param \ReflectionMethod $getter
     *
     * @return array
     */
    private function getGetterReturnMetadata(\ReflectionMethod $getter): array
    {
        $returnType = $getter->getReturnType();
        if ($returnType === null) {
            return [
                'kind'  => 'unknown',
                'class' => null,
            ];
        }
        $typeName = $returnType->getName();
        if ($typeName === 'array') {
            return [
                'kind'  => 'array',
                'class' => null,
            ];
        }
        if (!$returnType->isBuiltin()) {
            return [
                'kind'  => 'object',
                'class' => $typeName,
            ];
        }
        return [
            'kind'  => 'scalar',
            'class' => null,
        ];
    }

    /**
     * Найти дополнительные поля в исходных данных.
     *
     * @param array $data
     * @param string $prefix
     * @param array $knownRawPaths
     * @param array $extraFields
     */
    private function collectExtraFields(array $data, string $prefix, array $knownRawPaths, array &$extraFields)
    {
        foreach ($data as $name => $value) {
            $segment = is_int($name) ? '*' : $name;
            $path = $prefix === '' ? $segment : $prefix . '.' . $segment;
            $normalizedPath = $this->normalizeRawFieldPath($path);
            if (!isset($knownRawPaths[$normalizedPath])) {
                $extraFields[$normalizedPath] = [
                    'path'  => $path,
                    'value' => $value,
                ];
                continue;
            }
            if (!is_array($value)) {
                continue;
            }
            /*
             * Если SDK намеренно считает поле произвольным массивом и не описывает его внутренности — дальше не идём.
             * Например optional.
             */
            if (!$this->hasKnownChildFields($normalizedPath, $knownRawPaths)) {
                continue;
            }
            $this->collectExtraFields($value, $path, $knownRawPaths, $extraFields);
        }
    }

    /**
     * @param string $path
     * @param array $knownRawPaths
     *
     * @return bool
     */
    private function hasKnownChildFields(string $path, array $knownRawPaths): bool
    {
        $prefix = $path . '.';
        foreach ($knownRawPaths as $knownPath => $value) {
            if (strpos($knownPath, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Найти ключ массива с учётом нормализации.
     * Например, fiscal_number / fiscalNumber / fiscalnumber считаются одним полем.
     *
     * @param array $data
     * @param string $name
     * @param bool $found
     *
     * @return string|null
     */
    private function findArrayKey(array $data, string $name, bool &$found)
    {
        if (array_key_exists($name, $data)) {
            $found = true;
            return $name;
        }
        $normalizedName = $this->normalizeRawFieldPath($name);
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if ($this->normalizeRawFieldPath($key) === $normalizedName) {
                $found = true;
                return $key;
            }
        }
        $found = false;
        return null;
    }

    /**
     * Аналог array_is_list(), совместимый с PHP 7.2.
     *
     * @param array $data
     *
     * @return bool
     */
    private function isList(array $data): bool
    {
        $expectedKey = 0;
        foreach ($data as $key => $value) {
            if ($key !== $expectedKey) {
                return false;
            }
            ++$expectedKey;
        }
        return true;
    }

    /**
     * Нормализовать API field / PHP property.
     * Например  fiscal_number / fiscalNumber / fiscalnumber превращаются в fiscalnumber.
     *
     * @param string $path
     *
     * @return string
     */
    private function normalizeRawFieldPath(string $path): string
    {
        $parts = explode('.', $path);
        foreach ($parts as $key => $part) {
            if ($part === '*') {
                continue;
            }
            $parts[$key] = strtolower(preg_replace('/[^a-z0-9]/i', '', $part));
        }
        return implode('.', $parts);
    }
}