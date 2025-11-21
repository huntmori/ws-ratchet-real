<?php
namespace App\Trait;

trait EnumUtils
{
    /**
     * 값으로 Enum을 찾습니다.
     *
     * @param mixed $value 찾을 값
     * @return static|null 해당하는 Enum 또는 null
     */
    public static function tryFromValue(mixed $value): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return null;
    }

    /**
     * 값으로 Enum을 찾습니다. 없으면 예외 발생.
     *
     * @param mixed $value 찾을 값
     * @return static
     * @throws \ValueError
     */
    public static function fromValue(mixed $value): static
    {
        $enum = self::tryFromValue($value);
        if ($enum === null) {
            throw new \ValueError(sprintf(
                'Value "%s" is not valid for enum "%s"',
                $value,
                self::class
            ));
        }
        return $enum;
    }

    /**
     * 이름으로 Enum을 찾습니다.
     *
     * @param string $name Enum 케이스 이름
     * @return static|null
     */
    public static function tryFromName(string $name): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        return null;
    }

    /**
     * 이름으로 Enum을 찾습니다. 없으면 예외 발생.
     *
     * @param string $name Enum 케이스 이름
     * @return static
     * @throws \ValueError
     */
    public static function fromName(string $name): static
    {
        $enum = self::tryFromName($name);
        if ($enum === null) {
            throw new \ValueError(sprintf(
                'Name "%s" is not valid for enum "%s"',
                $name,
                self::class
            ));
        }
        return $enum;
    }

    /**
     * 모든 Enum 값들의 배열을 반환합니다.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_map(
            fn($case) => $case->value,
            self::cases()
        );
    }

    /**
     * 모든 Enum 이름들의 배열을 반환합니다.
     *
     * @return array
     */
    public static function names(): array
    {
        return array_map(
            fn($case) => $case->name,
            self::cases()
        );
    }

    /**
     * Enum을 [name => value] 형태의 배열로 반환합니다.
     *
     * @return array
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->name] = $case->value;
        }
        return $result;
    }

    /**
     * Enum을 [value => name] 형태의 배열로 반환합니다.
     *
     * @return array
     */
    public static function toArrayFlipped(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->name;
        }
        return $result;
    }

    /**
     * 특정 값이 Enum에 존재하는지 확인합니다.
     *
     * @param mixed $value 확인할 값
     * @return bool
     */
    public static function hasValue(mixed $value): bool
    {
        return self::tryFromValue($value) !== null;
    }

    /**
     * 특정 이름이 Enum에 존재하는지 확인합니다.
     *
     * @param string $name 확인할 이름
     * @return bool
     */
    public static function hasName(string $name): bool
    {
        return self::tryFromName($name) !== null;
    }

    /**
     * 랜덤한 Enum 케이스를 반환합니다.
     *
     * @return static
     */
    public static function random(): static
    {
        $cases = self::cases();
        return $cases[array_rand($cases)];
    }

    /**
     * Enum 케이스의 총 개수를 반환합니다.
     *
     * @return int
     */
    public static function count(): int
    {
        return count(self::cases());
    }
}