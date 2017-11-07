# yii2-login-blocker

Blocked login for few minutes after 3 wrong login times.

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

2. Use `loginBlocker`:

    ```php
    echo \Yii::$app->loginBlocker->check();
    echo \Yii::$app->loginBlocker->block();
    ```