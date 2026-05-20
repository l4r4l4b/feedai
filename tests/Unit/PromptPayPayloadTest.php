<?php

use App\Support\PromptPayPayload;

it('encodes a static payload for a Thai mobile number', function () {
    $payload = (string) PromptPayPayload::fromPhone('0812345678');

    expect($payload)
        ->toStartWith('000201')                           // Payload Format Indicator
        ->toContain('010211')                              // POI Method static
        ->toContain('A000000677010111')                    // PromptPay AID
        ->toContain('0066812345678')                       // Normalised phone (00 66 + 8-digit local)
        ->toContain('5303764')                             // Currency THB
        ->toContain('5802TH');                             // Country TH

    // CRC is the trailing 8 chars: "63" + length "04" + 4 hex digits
    expect(substr($payload, -8, 4))->toBe('6304');
});

it('normalises +66 international format to the same payload', function () {
    expect((string) PromptPayPayload::fromPhone('+66812345678'))
        ->toBe((string) PromptPayPayload::fromPhone('0812345678'));
});

it('emits POI=12 (dynamic) when an amount is provided', function () {
    $payload = (string) PromptPayPayload::fromPhoneAndAmountCents('0812345678', 35050);

    expect($payload)
        ->toContain('010212')                              // dynamic POI
        ->toContain('54')                                  // amount field tag
        ->toContain('350.50');                             // formatted to 2 decimals
});

it('produces a CRC that round-trips correctly', function () {
    // Generate, strip CRC, recompute, must match.
    $payload = (string) PromptPayPayload::fromPhone('0812345678');
    $body = substr($payload, 0, -4);
    $expectedCrc = substr($payload, -4);

    $crcMethod = (new ReflectionClass(PromptPayPayload::class))->getMethod('crc16Ccitt');

    $recomputed = $crcMethod->invoke(null, $body);

    expect($recomputed)->toBe($expectedCrc);
});

it('throws on empty or non-numeric input', function () {
    expect(fn () => PromptPayPayload::fromPhone(''))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => PromptPayPayload::fromPhone('abc'))
        ->toThrow(InvalidArgumentException::class);
});

it('accepts a 13-digit Thai national ID unchanged', function () {
    $payload = (string) PromptPayPayload::fromPhone('1234567890123');

    expect($payload)->toContain('1234567890123');
});
