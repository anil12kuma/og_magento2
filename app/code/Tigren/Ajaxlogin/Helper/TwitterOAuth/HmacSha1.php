<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2022 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\Ajaxlogin\Helper\TwitterOAuth;

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class HmacSha1 extends SignatureMethod
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "HMAC-SHA1";
    }

    /**
     * {@inheritDoc}
     */
    public function buildSignature(Request $request, Consumer $consumer, Token $token = null)
    {
        $signatureBase = $request->getSignatureBaseString();

        $parts = [$consumer->secret, null !== $token ? $token->secret : ""];

        $parts = Util::urlencodeRfc3986($parts);
        $key = implode('&', $parts);

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, true));
    }
}
