<?php

namespace App\Services;

class SimpleCaptcha
{
    public const SESSION_KEY = 'contact_captcha_answer';

    public const QUESTION_KEY = 'contact_captcha_question';

    public function generate(): string
    {
        $num1 = random_int(1, 12);
        $num2 = random_int(1, 12);
        $question = "{$num1} + {$num2}";

        session([
            self::SESSION_KEY => $num1 + $num2,
            self::QUESTION_KEY => $question,
        ]);

        return $question;
    }

    public function question(): ?string
    {
        return session(self::QUESTION_KEY);
    }

    public function validate(?string $answer): bool
    {
        $expected = session(self::SESSION_KEY);

        if ($expected === null || $answer === null || $answer === '') {
            return false;
        }

        return (int) $answer === (int) $expected;
    }

    public function forget(): void
    {
        session()->forget([self::SESSION_KEY, self::QUESTION_KEY]);
    }
}
