<?php

return [
    'create_fuel_tank.description' => 'Creates a fuel tank, a pool of Enjin Coins (ENJ) used to cover transaction fees for eligible users. Fuel tanks are customizable and operate based on defined rules to target specific actions or accounts that meet certain criteria. For more details, refer to the [Fuel Tanks documentation](https://docs.enjin.io/docs/using-fuel-tanks).',
    'create_fuel_tank.args.account' => 'The fuel tank will be created from this wallet account.',
    'destroy_fuel_tank.description' => <<<'MD'
Destroys a fuel tank, returning the storage deposit and any remaining balance to the fuel tank owner.  

**Prerequisites:**  
- The fuel tank must be frozen before it can be destroyed.  
- All Tank User Accounts must be removed prior to destruction.  

Failure to meet these conditions will cause the transaction to fail.  
MD,
    'fuel_tank.args.ruleSetId' => 'The ID of the rule set within the specified tank.',
    'fuel_tank.args.tankId' => 'The fuel tank\'s account address.',
    'add_account.description' => 'Adds an account to the Fuel Tank\'s [User Accounts](https://docs.enjin.io/docs/fuel-tank-pallet#tank-user-account).\nThis mutation can only be used by the tank owner unless the fuel tank is configured to allow accounts to add themselves. For more information, see the [User Account Management documentation](https://docs.enjin.io/docs/using-fuel-tanks#user-account-management).',
    'add_account.args.userId' => 'The wallet account address to be added to the fuel tank.',
    'mutate_fuel_tank.description' => 'Modifies an existing fuel tank\'s [User Account Management](https://docs.enjin.io/docs/using-fuel-tanks#user-account-management), [Coverage Policy](https://docs.enjin.io/docs/using-fuel-tanks#coverage-policy), and [Account Rules](https://docs.enjin.io/docs/using-fuel-tanks#account-rules).\nThe fuel tank must be frozen before it can be mutated. Use the `ScheduleMutateFreezeState` mutation to freeze the tank.',
    'batch_add_account.description' => 'Adds multiple accounts to the Fuel Tank\'s [User Accounts](https://docs.enjin.io/docs/fuel-tank-pallet#tank-user-account) in a single transaction.\nThis mutation can only be used by the tank owner unless the fuel tank is configured to allow accounts to add themselves. For more information, see the [User Account Management documentation](https://docs.enjin.io/docs/using-fuel-tanks#user-account-management).',
    'batch_add_account.args.userIds' => 'List of wallet account addresses that will be added to the fuel tank.',
    'remove_account.description' => 'Removes an account from the Fuel Tank\'s [User Accounts](https://docs.enjin.io/docs/fuel-tank-pallet#tank-user-account). Only the tank owner can remove accounts, except when the fuel tank is configured to allow accounts to add themselves; in this case, users can remove their own account.',
    'remove_account.args.userId' => 'The wallet account address that will be removed from the fuel tank.',
    'batch_remove_account.description' => 'Removes multiple accounts from the Fuel Tank\'s [User Accounts](https://docs.enjin.io/docs/fuel-tank-pallet#tank-user-account) in a single transaction. Only the tank owner can remove accounts, except when the fuel tank is configured to allow accounts to add themselves; in this case, users can remove their own account.',
    'batch_remove_account.args.userIds' => 'List of wallet account addresses that will be removed from the fuel tank.',
    'schedule_mutate_freeze_state.description' => 'Freezes or thaws a fuel tank or a rule set. If `ruleSetId` is provided, the specified rule set is targeted; otherwise, the fuel tank is targeted.',
    'schedule_mutate_freeze_state.args.isFrozen' => 'Determines the state of the target. Set to `true` to freeze or `false` to thaw the fuel tank or rule set.',
    'insert_rule_set.description' => <<<'MD'
Inserts a new rule set into a fuel tank or replaces an existing one.  

**Important Considerations:**  
- If replacing a rule set, a rule that stores data on user accounts might cause the operation to fail. [Learn more](https://docs.enjin.io/docs/fuel-tank-pallet#insert_rule_set).  
- Adding a rule set requires the fuel tank to be frozen; otherwise, the operation will fail.  
MD,
    'insert_rule_set.args.requireAccount' => 'Specifies if the caller must have a Tank User Account to dispatch transactions. If `true`, the caller must have an account, or the dispatch will fail. If `false`, the caller can dispatch without an account. [Learn more](https://docs.enjin.io/docs/fuel-tank-pallet#require-account).',
    'remove_rule_set.description' => <<<'MD'
Removes a rule set from a fuel tank.  

**Important Considerations:**  
- Rule sets storing data on any accounts cannot be removed and will result in an error. Use the `RemoveAccountRuleData` mutation to clear the data first.  
- Only the fuel tank owner can call this mutation.  
- The fuel tank must be frozen; otherwise, the mutation will fail.  
MD,
    'remove_account_rule_data.description' => 'Removes account rule data from a Fuel Tank User Account, if it exists. This includes dispatch rule data, such as the amount of fuel used for fuel budgets. The fuel tank or the rule set must be frozen to perform this operation. If neither is frozen, the transaction will fail.',
    'remove_account_rule_data.args.rule' => 'The rule data which will be removed from the tank user account.',
    'remove_account_rule_data.args.userId' => 'The wallet account address.',
    'force_set_consumption.description' => 'Forcefully sets the fuel consumption for either the tank\'s rule set budget or a specific user account\'s fuel budget.\nBy default, this mutation sets the fuel consumption of the tank\'s rule set budget. If the `userId` argument is provided, it adjusts the fuel consumption for the specified user account\'s fuel budget.',
    'force_set_consumption.args.lastResetBlock' => 'The block number to forcefully set as the last reset block for the target budget (user\'s or tank\'s rule set).',
    'force_set_consumption.args.totalConsumed' => 'The total fuel consumption to be forcefully set for the target budget (user\'s or tank\'s rule set).',
    'force_set_consumption.args.userId' => '(Optional) Specifies the user account whose fuel budget consumption will be set. If omitted, the rule set budget is targeted.',
    'dispatch.description' => 'Broadcasts a transaction through the fuel tank, which covers transaction fees and, if configured, provides a storage deposit. All calls are evaluated against the fuel tank\'s rule sets, and failure to meet the rules will result in an error.\nSome fuel tanks require the caller\'s account to be added to the tank\'s [User Accounts](https://docs.enjin.io/docs/fuel-tank-pallet#tank-user-account) to dispatch. To self-add the caller to the tank and dispatch simultaneously in a single transaction, use the `DispatchAndTouch` mutation.\n[Learn more about dispatching calls using a fuel tank](https://docs.enjin.io/docs/using-fuel-tanks#dispatching-a-call-using-a-fuel-tank).',
    'dispatch.args.paysRemainingFee' => 'Pays remaining fee flag.',
    'dispatch.args.ruleSetId' => 'The ID of the rule set to dispatch with, within the specified tank.',
    'dispatch_and_touch.description' => 'Broadcasts a transaction through the fuel tank, covering transaction fees and, if configured, providing a storage deposit. If the caller\'s account is not already added to the fuel tank\'s [User Accounts](https://docs.enjin.io/docs/fuel-tank-pallet#tank-user-account), this mutation adds the account before dispatching the transaction.\nTo dispatch without adding the account, use the `Dispatch` mutation instead.\nAll calls are evaluated against the fuel tank\'s rule sets, and failure to meet the rules will result in an error.[Learn more about dispatching calls using a fuel tank](https://docs.enjin.io/docs/using-fuel-tanks#dispatching-a-call-using-a-fuel-tank).',
];
