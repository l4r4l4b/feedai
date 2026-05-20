<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Builds the EMVCo-compliant PromptPay payload string for a Thai mobile number.
 *
 * The Bank of Thailand's PromptPay standard encodes each field as a triplet:
 * <tag (2)><length (2)><value (length)>. Phone numbers are prefixed with
 * country code 0066 and normalised to 13 digits. The payload is closed by a
 * CRC-16/CCITT-FALSE checksum field (tag 63).
 *
 * Example: phone "0812345678" → 13-digit value "0066812345678".
 *
 * This is the same string a Thai banking app expects when scanning a QR.
 */
final class PromptPayPayload
{
    private const AID_PROMPTPAY = 'A000000677010111';

    private function __construct(public readonly string $payload) {}

    /**
     * Generate a static (no amount) payload from a Thai phone or national ID.
     */
    public static function fromPhone(string $phone): self
    {
        $value = self::normalisePhone($phone);

        return self::build($value, amount: null);
    }

    /**
     * Generate a dynamic payload with a fixed amount in THB (cents resolution).
     */
    public static function fromPhoneAndAmountCents(string $phone, int $amountCents): self
    {
        $value = self::normalisePhone($phone);
        $amountThb = number_format($amountCents / 100, 2, '.', '');

        return self::build($value, amount: $amountThb);
    }

    public function __toString(): string
    {
        return $this->payload;
    }

    private static function build(string $phoneValue, ?string $amount): self
    {
        // Merchant Account Info (tag 29) is a nested TLV containing the AID
        // and the target identifier (phone wrapped in tag 01).
        $merchantInfo = self::tlv('00', self::AID_PROMPTPAY).self::tlv('01', $phoneValue);

        $body = self::tlv('00', '01')                                 // Payload Format Indicator
            .self::tlv('01', $amount === null ? '11' : '12')         // POI Method: 11 static, 12 dynamic
            .self::tlv('29', $merchantInfo)                          // Merchant Account Info
            .self::tlv('53', '764')                                  // Transaction Currency: THB
            .($amount !== null ? self::tlv('54', $amount) : '')      // Optional amount
            .self::tlv('58', 'TH');                                  // Country Code

        // CRC field is calculated INCLUDING its own tag+length but with the
        // value placeholder substituted as '0000' during computation.
        $payloadForCrc = $body.'6304';
        $crc = self::crc16Ccitt($payloadForCrc);

        return new self($body.'63'.'04'.$crc);
    }

    /**
     * Convert phone like "0812345678" or "+66812345678" into the 13-digit
     * PromptPay representation prefixed with "0066". National IDs (13 digits)
     * are passed through unchanged.
     */
    private static function normalisePhone(string $input): string
    {
        $digits = preg_replace('/\D/', '', $input) ?? '';

        if ($digits === '') {
            throw new InvalidArgumentException('PromptPay identifier must contain digits.');
        }

        // 13-digit Thai national ID stays as-is.
        if (strlen($digits) === 13 && ! str_starts_with($digits, '0')) {
            return $digits;
        }

        // Strip leading 0 (domestic format) before prefixing country code.
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = substr($digits, 1);
        }

        // Already prefixed with 66 — leave alone.
        if (str_starts_with($digits, '66')) {
            $digits = substr($digits, 2);
        }

        $countryPrefixed = '0066'.$digits;

        if (strlen($countryPrefixed) !== 13) {
            throw new InvalidArgumentException(
                "PromptPay phone normalisation produced unexpected length: {$countryPrefixed}",
            );
        }

        return $countryPrefixed;
    }

    private static function tlv(string $tag, string $value): string
    {
        return $tag.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    /**
     * CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF, no reflect, no xorout).
     * Returns 4 uppercase hex chars as required by the EMV QR standard.
     */
    private static function crc16Ccitt(string $data): string
    {
        $crc = 0xFFFF;

        foreach (str_split($data) as $char) {
            $crc ^= ord($char) << 8;

            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
