<?php

class GKashSignature
{
    public static function normalizeAmount($amount)
    {
        return number_format((float) $amount, 2, '.', '');
    }

    public static function amountWithoutDot($amount)
    {
        return str_replace('.', '', self::normalizeAmount($amount));
    }

    public static function normalizeStatus($status)
    {
        $status = trim((string) $status);
        if (preg_match('/^(\d{2})/', $status, $matches)) {
            return $matches[1];
        }

        return $status;
    }

    public static function buildSignatureString(array $parts)
    {
        $parts = array_map('strval', $parts);
        return strtoupper(implode(';', $parts));
    }

    public static function hashSignatureString($signatureString)
    {
        return hash('sha512', strtoupper($signatureString));
    }

    public static function generateCheckoutSignature($signatureKey, $cid, $cartId, $amount, $currency)
    {
        $string = self::buildSignatureString(array(
            $signatureKey,
            $cid,
            $cartId,
            self::amountWithoutDot($amount),
            $currency,
        ));

        return self::hashSignatureString($string);
    }

    public static function generateCallbackSignature($signatureKey, $cid, $poid, $cartId, $amount, $currency, $status)
    {
        $string = self::buildSignatureString(array(
            $signatureKey,
            $cid,
            $poid,
            $cartId,
            self::amountWithoutDot($amount),
            $currency,
            $status,
        ));

        return self::hashSignatureString($string);
    }

    public static function verifyCheckoutSignature($signatureKey, $cid, $cartId, $amount, $currency, $signature)
    {
        $expected = self::generateCheckoutSignature($signatureKey, $cid, $cartId, $amount, $currency);
        return self::equals($expected, $signature);
    }

    public static function verifyCallbackSignature($signatureKey, $cid, $poid, $cartId, $amount, $currency, $status, $signature)
    {
        $expected = self::generateCallbackSignature($signatureKey, $cid, $poid, $cartId, $amount, $currency, $status);
        return self::equals($expected, $signature);
    }

    public static function isSuccessfulStatus($status)
    {
        $normalized = self::normalizeStatus($status);
        return $normalized === '88' || trim((string) $status) === '88 - Transferred';
    }

    protected static function equals($expected, $given)
    {
        $expected = strtolower(trim((string) $expected));
        $given = strtolower(trim((string) $given));

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $given);
        }

        return $expected === $given;
    }
}

