<?php

return [
    'fuel_tank.description' => 'A fuel tank.',
    'fuel_tank.field.wallet' => 'The wallet account for the fuel tank.',
    'fuel_tank.field.owner_wallet' => 'The wallet account that owns the fuel tank.',
    'fuel_tank.field.name' => 'The fuel tank name.',
    'fuel_tank.field.reservesExistentialDeposit' => '(DEPRECATED) The flag for existential deposit.',
    'fuel_tank.field.reservesAccountCreationDeposit' => <<<'MD'
Determines how accounts are added to the Fuel Tank User Accounts and whether the Fuel Tank covers the storage deposit costs. [Learn more](https://docs.enjin.io/docs/using-fuel-tanks#user-account-management).  

Options:  
1. **Not provided**  
   Only the Fuel Tank owner can add accounts to the Fuel Tank User Accounts.  

2. **Set to `false`**  
   Accounts can add themselves to the Fuel Tank User Accounts, but the Fuel Tank does not provide funds for the Tank User Account Storage Deposit.  

3. **Set to `true`**  
   Accounts can add themselves to the Fuel Tank User Accounts, and the Fuel Tank covers the required funds for the Tank User Account Storage Deposit.  
MD,
    'fuel_tank.field.providesDeposit' => '(DEPRECATED) The flag for deposit.',
    'fuel_tank.field.isFrozen' => 'The flag for frozen state.',
    'fuel_tank.field.accountCount' => 'The number of accounts.',
    'fuel_tank.field.accounts' => 'The fuel tank accounts.',
    'fuel_tank.field.accountRules' => 'The fuel tank account rules.',
    'fuel_tank.field.dispatchRules' => 'The fuel tank dispatch rules.',
    'fuel_tank.field.coveragePolicy' => <<<'MD'
Defines the coverage scope for the Fuel Tank. [Learn more](https://docs.enjin.io/docs/using-fuel-tanks#coverage-policy).  

Options:  
1. **FEES** *(Default)*  
   The Fuel Tank subsidizes only transaction fees.  

2. **FEES_AND_DEPOSIT**  
   The Fuel Tank covers both transaction fees and any storage deposit required by the dispatched call.  
MD,
    'fuel_tank_rule.description' => 'The fuel tank rules.',
    'fuel_tank_rule.field.rule' => 'The fuel tank rule.',
    'fuel_tank_rule.field.value' => 'The rule values.',
];
