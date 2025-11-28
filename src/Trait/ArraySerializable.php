<?php

namespace App\Trait;

use App\Attribute\ArrayKeyIgnore;
use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use BackedEnum;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;
use ReflectionNamedType;
use UnitEnum;

/**
 * ArraySerializable Trait
 *
 * 객체와 배열/JSON 간의 직렬화/역직렬화를 제공하는 트레이트
 *
 * 주요 기능:
 * - JSON/배열에서 객체로 변환 (fromJson, fromJsonArray)
 * - 객체를 배열/JSON으로 변환 (toArray, toJson)
 * - Attribute 기반 키 매핑 (FromArrayKey, ToArrayKey, ArrayKeyIgnore)
 * - 다양한 타입 자동 변환 (Enum, DateTime, 중첩 객체 등)
 */
trait ArraySerializable
{
    /**
     * JSON 문자열 또는 연관 배열에서 객체 생성
     */
    public static function fromJson(string|array $data): static
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
        }

        $instance = new static();
        $reflection = new ReflectionClass($instance);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(FromArrayKey::class);

            // JsonIgnore가 있으면 건너뛰기
            if (!empty($property->getAttributes(ArrayKeyIgnore::class))) {
                continue;
            }

            $jsonKey = $property->getName();
            $required = false;

            // FromJsonKey 어트리뷰트가 있으면 사용
            if (!empty($attributes)) {
                $attribute = $attributes[0]->newInstance();
                $jsonKey = $attribute->key;
                $required = $attribute->required;
            }

            if (!array_key_exists($jsonKey, $data)) {
                if ($required) {
                    throw new InvalidArgumentException("Required key '$jsonKey' is missing");
                }
                continue;
            }

            $value = $data[$jsonKey];

            // 타입에 따라 변환
            $value = self::convertValue($property, $value);

            $property->setAccessible(true);
            $property->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * 객체를 연관 배열로 변환
     */
    public function toArray(): array
    {
        $result = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            // JsonIgnore가 있으면 건너뛰기
            if (!empty($property->getAttributes(ArrayKeyIgnore::class))) {
                continue;
            }

            $property->setAccessible(true);

            // 초기화되지 않은 속성은 건너뛰기
            if (!$property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);

            // ToJsonKey 어트리뷰트 확인
            $attributes = $property->getAttributes(ToArrayKey::class);
            $jsonKey = $property->getName();
            $exclude = false;

            if (!empty($attributes)) {
                $attribute = $attributes[0]->newInstance();
                $jsonKey = $attribute->key;
                $exclude = $attribute->exclude;
            }

            if ($exclude) {
                continue;
            }

            // 값 변환
            $result[$jsonKey] = self::serializeValue($value);
        }

        return $result;
    }

    /**
     * 객체를 JSON 문자열로 변환
     */
    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * JSON 문자열을 객체 배열로 변환
     */
    public static function fromJsonArray(string|array $data): array
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Expected array');
        }

        return array_map(fn($item) => static::fromJson($item), $data);
    }

    /**
     * 속성 값을 타입에 맞게 변환
     */
    private static function convertValue(ReflectionProperty $property, mixed $value): mixed
    {
        $type = $property->getType();

        if ($type === null || $value === null) {
            return $value;
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();

            return match ($typeName) {
                'int' => (int) $value,
                'float' => (float) $value,
                'string' => (string) $value,
                'bool' => (bool) $value,
                'array' => (array) $value,
                default => self::convertComplexType($typeName, $value)
            };
        }

        return $value;
    }

    /**
     * 복잡한 타입 변환 (Enum, DateTime, 객체 등)
     */
    private static function convertComplexType(string $typeName, mixed $value): mixed
    {
        // DateTime 처리
        if ($typeName === 'DateTime' || is_subclass_of($typeName, \DateTime::class)) {
            if (is_string($value)) {
                try {
                    return new \DateTime($value);
                } catch (\Exception $e) {
                    throw new InvalidArgumentException(
                        "Cannot convert '{$value}' to DateTime: " . $e->getMessage()
                    );
                }
            }
            if ($value instanceof \DateTime) {
                return $value;
            }
            throw new InvalidArgumentException(
                "Cannot convert value of type " . gettype($value) . " to DateTime"
            );
        }

        // DateTimeImmutable 처리
        if ($typeName === 'DateTimeImmutable' || is_subclass_of($typeName, \DateTimeImmutable::class)) {
            if (is_string($value)) {
                try {
                    return new \DateTimeImmutable($value);
                } catch (\Exception $e) {
                    throw new InvalidArgumentException(
                        "Cannot convert '{$value}' to DateTimeImmutable: " . $e->getMessage()
                    );
                }
            }
            if ($value instanceof \DateTimeImmutable) {
                return $value;
            }
            throw new InvalidArgumentException(
                "Cannot convert value of type " . gettype($value) . " to DateTimeImmutable"
            );
        }

        // Enum 처리
        if (enum_exists($typeName)) {
            if (is_subclass_of($typeName, BackedEnum::class)) {
                return $typeName::from($value);
            }
            if (is_subclass_of($typeName, UnitEnum::class)) {
                return constant("$typeName::$value");
            }
        }

        // JsonSerializable trait을 사용하는 클래스
        if (method_exists($typeName, 'fromJson')) {
            return $typeName::fromJson($value);
        }

        return $value;
    }

    /**
     * 값을 직렬화 가능한 형태로 변환
     */
    private static function serializeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn($v) => self::serializeValue($v), $value);
        }

        // DateTime 객체를 ISO 8601 문자열로 변환
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if (method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        // 객체를 문자열로 변환 시도
        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        // 기본적으로 객체는 클래스명 반환
        if (is_object($value)) {
            return ['_class' => get_class($value)];
        }

        return $value;
    }
}