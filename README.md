# yii2-login-blocker (Not ready yet)

[![Latest Stable Version](https://poser.pugx.org/ognyk/yii2-login-blocker/v/stable)](https://packagist.org/packages/ognyk/yii2-login-blocker)
[![Total Downloads](https://poser.pugx.org/ognyk/yii2-login-blocker/downloads)](https://packagist.org/packages/ognyk/yii2-login-blocker)
[![License](https://poser.pugx.org/ognyk/yii2-login-blocker/license)](https://packagist.org/packages/ognyk/yii2-login-blocker)

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
            'class' => '\ognyk\loginblocker\LoginBlocker',
            'time' => 300,              //  optional
            'wrong_login_number' => 3,  //  optional
        ]
    ]
    ```

2. Methods `loginBlocker`:

    ```php
    /* Check if user can login */
    \Yii::$app->loginBlocker->check();
    
    /* Increment when wrong login or password */
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