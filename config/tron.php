<?php

return [

    /*
     * TronGrid.io API key, it's optional but recommended.
     * https://www.trongrid.io/dashboard/keys
     */
    'trongrid_api_key' => env('TRONGRID_API_KEY'),

    /*
     * Tron Nodes URL
     */
    'full_node' => 'https://api.trongrid.io',
    'solidity_node' => 'https://api.trongrid.io',

    /*
     * Sets the handler to be used when Tron Wallet
     * receives a new transaction.
     */
    'webhook_handler' => \Mollsoft\LaravelTronModule\Handlers\EmptyWebhookHandler::class,

    /*
     * Set model class for both TronWallet, TronAddress, TronTrc20,
     * to allow more customization.
     *
     * TronWallet model must be or extend `Mollsoft\LaravelTronModule\Models\TronWallet::class`
     * TronAddress model must be or extend `Mollsoft\LaravelTronModule\Models\TronAddress::class`
     * TronTrc20 model must be or extend `Mollsoft\LaravelTronModule\Models\TronTrc20::class`
     * TronTransaction model must be or extend `Mollsoft\LaravelTronModule\Models\TronTransaction::class`
     */
    'models' => [
        'wallet' => \Mollsoft\LaravelTronModule\Models\TronWallet::class,
        'address' => \Mollsoft\LaravelTronModule\Models\TronAddress::class,
        'trc20' => \Mollsoft\LaravelTronModule\Models\TronTRC20::class,
    ]
];
