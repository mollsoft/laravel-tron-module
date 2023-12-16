<?php

return [
    /*
     * Touch Synchronization System (TSS) config
     * If there are many addresses in the system, we synchronize only those that have been touched recently.
     * You must update touch_at in TronAddress, if you want sync here.
     */
    'touch' => [
        /*
         * Is system enabled?
         */
        'enabled' => false,

        /*
         * The time during which the address is synchronized after touching it (in seconds).
         */
        'waiting_seconds' => 3600,
    ],

    /*
     * Sets the handler to be used when Tron Wallet
     * receives a new deposit.
     */
    'webhook_handler' => \Mollsoft\LaravelTronModule\Handlers\EmptyWebhookHandler::class,

    /*
     * Set model class for both TronWallet, TronAddress, TronTrc20,
     * to allow more customization.
     *
     * TronApi model must be or extend `Mollsoft\LaravelTronModule\Api\Api::class`
     * TronNode model must be or extend `Mollsoft\LaravelTronModule\Models\TronNode::class`
     * TronWallet model must be or extend `Mollsoft\LaravelTronModule\Models\TronWallet::class`
     * TronAddress model must be or extend `Mollsoft\LaravelTronModule\Models\TronAddress::class`
     * TronTrc20 model must be or extend `Mollsoft\LaravelTronModule\Models\TronTrc20::class`
     * TronTransaction model must be or extend `Mollsoft\LaravelTronModule\Models\TronTransaction::class`
     * TronDeposit model must be or extend `Mollsoft\LaravelTronModule\Models\TronDeposit::class`
     */
    'models' => [
        'api' => \Mollsoft\LaravelTronModule\Api\Api::class,
        'node' => \Mollsoft\LaravelTronModule\Models\TronNode::class,
        'wallet' => \Mollsoft\LaravelTronModule\Models\TronWallet::class,
        'address' => \Mollsoft\LaravelTronModule\Models\TronAddress::class,
        'trc20' => \Mollsoft\LaravelTronModule\Models\TronTRC20::class,
        'transaction' => \Mollsoft\LaravelTronModule\Models\TronTransaction::class,
        'deposit' => \Mollsoft\LaravelTronModule\Models\TronDeposit::class,
    ]
];
