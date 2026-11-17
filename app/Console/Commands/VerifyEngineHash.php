<?php

namespace App\Console\Commands;

use App\Services\OtcPriceEngine;
use Illuminate\Console\Command;

/**
 * Deploy gate for the price engine's hash.
 *
 * Hostinger's PHP returns non-standard values from hash('crc32b') on
 * every PHP version 8.1-8.5 (crc32b('123456789') = cbf43926 instead
 * of the standard fc891918), so the engine uses its own pure-PHP
 * CRC-32/BZIP2 (OtcPriceEngine::crc32bHex). The old
 * `php -r "echo hash('crc32b', '123456789');"` gate WILL FAIL on
 * the server and must NOT be used — run this command instead.
 *
 * Usage:
 *   php artisan engine:verify-hash
 *
 * Exit code 0 only when the engine's CRC for '123456789' is
 * fc891918 and for '' is 00000000, AND every pxHashU32 sample
 * below matches its expected value exactly. The pxHashU32 samples
 * are CHECKED, not just printed — including an overflowing one
 * ('px2:w:EURUSD:10:1'): its pre-second-multiply value
 * x = 4,283,491,196 exceeds 4,151,468,660, the point where
 * x * 0x846CA68B overflows PHP_INT_MAX and the old direct
 * multiply silently went through float arithmetic. Expected
 * values were computed in exact arithmetic (Node BigInt) and
 * cross-checked against the JS harness's Math.imul values
 * (price-process-verify.mjs).
 */
class VerifyEngineHash extends Command
{
    protected $signature = 'engine:verify-hash';

    protected $description = 'Verify the price engine pure-PHP CRC-32/BZIP2 check values (deploy gate — do NOT use php -r hash(\'crc32b\') on this server).';

    public function handle(): int
    {
        $checks = [
            '123456789' => 'fc891918',
            '' => '00000000',
        ];

        $ok = true;
        foreach ($checks as $input => $expected) {
            $got = OtcPriceEngine::crc32bHex($input);
            $label = $input === '' ? "'' (empty)" : "'{$input}'";
            if ($got === $expected) {
                $this->info("PASS crc32bHex({$label}) = {$got}");
            } else {
                $ok = false;
                $this->error("FAIL crc32bHex({$label}) = {$got}, expected {$expected}");
            }
        }

        $this->newLine();
        $this->info('pxHashU32 samples (engine CRC-32/BZIP2 + splitmix32 finalizer):');
        // Expected values computed in exact arithmetic (Node BigInt),
        // cross-checked against the JS harness Math.imul values.
        // 'px2:w:EURUSD:10:1' is the overflowing sample: its
        // pre-second-multiply value x = 4283491196 exceeds
        // 4151468660, so x * 0x846CA68B overflows PHP_INT_MAX.
        $hashChecks = [
            'px2:w:EURUSD:10:1' => 4240249065,
            '123456789' => 2549466132,
            'px2:w:EURUSD:10:5' => 4284989179,
            'px2:regime:EURUSD:0:type' => 2425760299,
            'BTCUSD' => 2183901671,
        ];
        foreach ($hashChecks as $key => $expected) {
            $h = OtcPriceEngine::pxHashU32For($key);
            $label = "pxHashU32('{$key}') = {$h} (0x" . sprintf('%08x', $h) . ')';
            if ($h === $expected) {
                $this->info("  PASS {$label}");
            } else {
                $ok = false;
                $this->error("  FAIL {$label}, expected {$expected} (0x" . sprintf('%08x', $expected) . ')');
            }
        }

        $this->newLine();
        if ($ok) {
            $this->info('ENGINE HASH OK: pure-PHP CRC-32/BZIP2 and pxHashU32 finalizer match the standard check values.');
            return self::SUCCESS;
        }

        $this->error('ENGINE HASH FAILED: the pure-PHP CRC or pxHashU32 finalizer does not match the standard check values — do not deploy.');
        return self::FAILURE;
    }
}
