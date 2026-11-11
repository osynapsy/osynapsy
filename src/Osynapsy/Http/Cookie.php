<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http;

class Cookie
{
    public const SAMESITE_LAX    = 'Lax';
    public const SAMESITE_STRICT = 'Strict';
    public const SAMESITE_NONE   = 'None';

    protected string $name;
    protected string $value;
    protected int $expiry;
    protected bool $httpOnly;
    protected bool $secure;
    protected string $sameSite;
    protected ?string $host = null;
    protected bool $shareWithSubdomains = false;

    public function __construct(
        string $name = '',
        string $value = '',
        int $expiry = 86400, 
        bool $httpOnly = true, 
        bool $secure = true, 
        string $sameSite = self::SAMESITE_LAX
    ) {
        $this->name     = $name;
        $this->value    = $value;
        $this->expiry   = time() + $expiry;
        $this->httpOnly = $httpOnly;
        $this->secure   = $secure;
        $this->sameSite = $sameSite;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function setExpiry(int $expiry): self
    {
        $this->expiry = time() + $expiry;
        return $this;
    }

    public function setHttpOnly(bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function setSecure(bool $secure): self
    {
        $this->secure = $secure;
        return $this;
    }

    public function setSameSite(string $sameSite): self
    {
        if (!in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE])) {
            throw new \InvalidArgumentException("Invalid SameSite value: {$sameSite}");
        }
        $this->sameSite = $sameSite;
        return $this;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setShareWithSubdomains(bool $shareWithSubdomains): self
    {
        $this->shareWithSubdomains = $shareWithSubdomains;
        return $this;
    }

    /**
     * Invia il cookie.
     */
    public function send(): bool
    {
        if (headers_sent()) {
            return false;
        }

        $domain = $this->resolveDomain();
        $expiration = ($this->expiry > 0) ? $this->expiry : time() - 3600;

        $cookieOptions = [
            'expires'  => $expiration,
            'path'     => '/',
            'domain'   => $domain,
            'secure'   => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        return setcookie($this->name, $this->value, $cookieOptions);
    }

    /**
     * Cancelella il cookie impostando una scadenza nel passato.
     */
    public function delete(): bool
    {
        if (headers_sent()) {
            return false;
        }

        $domain = $this->resolveDomain();

        $cookieOptions = [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $domain,
            'secure'   => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        return setcookie($this->name, '', $cookieOptions);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Verifica che il cookie sia ben formato.
     */
    public function isValid(): bool
    {
        return $this->name !== '' && $this->value !== '';
    }

    /**
     * Rappresentazione serializzabile (utile per debug o logging).
     */
    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'value'    => $this->value,
            'expiry'   => $this->expiry,
            'httpOnly' => $this->httpOnly,
            'secure'   => $this->secure,
            'sameSite' => $this->sameSite,
            'host'     => $this->host ?? '',
        ];
    }

    /**
     * Rappresentazione leggibile (utile per debug).
     */
    public function __toString(): string
    {
        return sprintf(
            "%s=%s; expires=%d; path=/; domain=%s; httponly=%s; secure=%s; samesite=%s",
            $this->name,
            $this->value,
            $this->expiry,
            $this->host ?? '',
            $this->httpOnly ? 'true' : 'false',
            $this->secure   ? 'true' : 'false',
            $this->sameSite
        );
    }

    private function resolveDomain(): string
    {
        if (!empty($this->host)) {
            return $this->host;
        }

        if ($this->shareWithSubdomains) {
            $serverName = $_SERVER['SERVER_NAME'] ?? null;
            if (!$serverName) {
                return '';
            }

            $domainPart = explode('.', $serverName);
            if (count($domainPart) > 2) {
                array_shift($domainPart);
            }
            return '.' . implode('.', $domainPart);
        }

        return '';
    }
}
