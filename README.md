# yii2-login-blocker

[![Latest Stable Version](https://poser.pugx.org/ognyk/yii2-login-blocker/v/stable)](https://packagist.org/packages/ognyk/yii2-login-blocker)
[![Total Downloads](https://poser.pugx.org/ognyk/yii2-login-blocker/downloads)](https://packagist.org/packages/ognyk/yii2-login-blocker)
[![License](https://poser.pugx.org/ognyk/yii2-login-blocker/license)](https://packagist.org/packages/ognyk/yii2-login-blocker)

Block/ban login for few minutes after 3 wrong login times.

## Installation

The preferred way to install this extension is through [Composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist ognyk/yii2-login-blocker
```

or add

```json
"ognyk/yii2-login-blocker": "*"
```

to the `require` section of your `composer.json` file.

## Usage

1. Add `loginBlocker` component to your [Yii2 configuration](http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html#application-configurations)
like this:

    ```php
    'components' => [
        'loginBlocker' => [
            'class' => '\ognyk\loginblocker\LoginBlocker'
        ]
    ]
    ```

2. Methods `loginBlocker`:

    ```php
    /* Check if user can login */
    \Yii::$app->loginBlocker->check();
    
    /* Increment counter when wrong login or password */
    \Yii::$app->loginBlocker->block();
    ```
3. Use `loginBlocker`:

    ```php
    if (!\Yii::$app->loginBlocker->check()) {
        return ['error' => 'time_block'];
    }
    
    if ($model->login()) {
        // ... good action here
    } else {
        \Yii::$app->loginBlocker->block();
        
        return ['error' => 'wrong_credentials'];
    }
    ```

## Advanced config

1. More parameters:

    ```php
    'components' => [
        'loginBlocker' => [
            'class' => '\ognyk\loginblocker\LoginBlocker',
            'time' => 300,              // Time to block/ban user in seconds (default 300 sec)
            'wrong_login_number' => 3,  // Number of wrong attempts (default 3 times)
        ]
    ]
    ```   
    
2. Notification of block/ban by e-mail:

    To use notification by e-mail configure `\Yii::$app->mailer.`

    All parameters without `mails` are optional.

    ```php
    'components' => [
        'loginBlocker' => [
            'class' => '\ognyk\loginblocker\LoginBlocker',
            'mail' => [
                'subject' => 'New subject with user IP {ip}',
                'content' => 'User IP {ip}<br>Date: {date}<b>{params}',
                'sender' => [
                    'name' => 'Cezar II',
                    'mail' => 'mail@mail.com',
                ],
                'mails' => [
                    'admin1@mail.com',
                    'admin2@mail.com',
                    'admin3@mail.com',
                ],
            ]
        ]
    ]
    ```
    
3. More information from login action:

    You can pass custom params to your alert e-mail.
    
    ```php
    $params = [
        'Username' => 'Cezar V',
        'Server' => 'torr-2378-45'
    ];
    
    \Yii::$app->loginBlocker->check($params)
    ``` 