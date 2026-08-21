<?php

namespace App\Support;

use Illuminate\Http\Request;

final class LeadReturnUrl
{
    /**
     * Sirf Lead Table aur Lead Kanban URL ko
     * valid return destination maana jayega.
     *
     * Isse malicious external return_url ke through
     * open redirect bhi prevent hota hai.
     */
    public static function resolve(
        Request $request,
        string $fallback
    ): string {
        $candidate = trim(
            (string) $request->input(
                'return_url',
                ''
            )
        );

        /*
         * Validation fail ke baad Laravel old input me
         * return_url ho sakta hai.
         */
        if (
            $candidate === ''
            && $request->hasSession()
        ) {
            $candidate = trim(
                (string) $request
                    ->session()
                    ->getOldInput(
                        'return_url',
                        ''
                    )
            );
        }

        if ($candidate === '') {
            return $fallback;
        }

        /*
         * Relative URL support.
         */
        if (
            str_starts_with(
                $candidate,
                '/'
            )
        ) {
            $candidate = url(
                $candidate
            );
        }

        $parts = parse_url(
            $candidate
        );

        if ($parts === false) {
            return $fallback;
        }

        $host =
            $parts['host']
            ?? '';

        /*
         * Current CRM domain ke bahar redirect
         * allow nahi karna.
         */
        if (
            $host === ''
            || strcasecmp(
                $host,
                $request->getHost()
            ) !== 0
        ) {
            return $fallback;
        }

        $path =
            '/'
            . trim(
                (string) (
                    $parts['path']
                    ?? ''
                ),
                '/'
            );

        /*
         * Sirf ye do final source views valid hain:
         *
         * /lead
         * /lead/kanban
         */
        if (
            !in_array(
                $path,
                [
                    '/lead',
                    '/lead/kanban',
                ],
                true
            )
        ) {
            return $fallback;
        }

        return $candidate;
    }
}