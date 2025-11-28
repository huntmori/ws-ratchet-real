<?php

namespace App\Trait;

/**
 * Buildable Trait
 *
 * 빌더 패턴을 지원하는 트레이트
 *
 * 사용 예시:
 * <code>
 * $user = User::builder()
 *     ->name('홍길동')
 *     ->email('hong@example.com')
 *     ->build();
 * </code>
 *
 * 주요 기능:
 * - 메서드 체이닝을 통한 객체 생성
 * - 매직 메서드(__call)를 통한 동적 setter/getter
 * - 유연한 객체 생성 패턴 제공
 */
trait Buildable
{
    /**
     * Builder 인스턴스를 생성합니다.
     */
    public static function builder(): static
    {
        return new static();
    }

    /**
     * 매직 메서드로 getter/setter를 동적으로 처리합니다.
     *
     * @param string $method 호출된 메서드 이름
     * @param array $arguments 전달된 인자
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        // 프로퍼티 이름 추출 (첫 글자를 소문자로)
        $property = lcfirst($method);

        // 프로퍼티가 존재하는지 확인
        if (!property_exists($this, $property)) {
            throw new \BadMethodCallException(
                sprintf('Property "%s" does not exist in class "%s"', $property, static::class)
            );
        }

        // 매개변수가 없으면 getter (값 반환)
        if (empty($arguments)) {
            return $this->$property;
        }

        // 매개변수가 있으면 setter (값 할당 후 $this 반환 - 체이닝 지원)
        $this->$property = $arguments[0];
        return $this;
    }

    /**
     * 현재 인스턴스를 반환합니다 (빌더 패턴의 마지막 단계).
     */
    public function build(): self
    {
        return $this;
    }
}