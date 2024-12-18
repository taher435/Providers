<?php

namespace SocialiteProviders\Mediawiki;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'MEDIAWIKI';

    protected function getMediawikiUrl()
    {
        return $this->getConfig('base_url');
    }

    public static function additionalConfigKeys(): array
    {
        return ['base_url'];
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getMediawikiUrl().'/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getMediawikiUrl().'/oauth2/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->getMediawikiUrl().'/oauth2/resource/profile',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['sub'],
            'nickname' => $user['username'],
            'name'     => Arr::get($user, 'realname'),
            'email'    => Arr::get($user, 'email'),
            'avatar'   => null,
        ]);
    }
}
