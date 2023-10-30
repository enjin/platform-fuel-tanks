<?php

return [
    'account_rule.description' => 'The fuel tank account rules.',
    'account_rule.field.whitelistedCallers' => 'The wallet accounts that are allowed to use the fuel tank.',
    'account_rule.field.requireToken' => 'The wallet account must have a specific token in their wallet to use the fuel tank.',
    'dispatch_rule.description' => 'The fuel tank dispatch rules.',
    'dispatch_rule.field.whitelistedCollections' => 'The list of collections that can be used in the fuel tank.',
    'dispatch_rule.field.maxFuelBurnPerTransaction' => 'The maximum amount of fuel can be used per transaction.',
    'fuel_budget.description' => 'The rule for fuel budget.',
    'fuel_budget.field.amount' => 'The amount of fuel.',
    'fuel_budget.field.resetPeriod' => 'The period when the amount will reset.',
    'require_token.description' => 'The rule for requiring a specific token.',
    'require_token.field.collectionId' => 'The collection chain ID.',
    'require_token.field.tokenId' => 'The token chain ID.',
    'fuel_tank_mutation.description' => 'The fuel tank input fields.',
    'dispatch.description' => 'The dispatch call.',
    'dispatch.field.query' => "The GraphQL query. It's required to query the 'id' and 'encodedData' from the result.",
    'dispatch.field.variables' => 'The GraphQL query variables.',
    'permitted_extrinsics.description' => 'The list of permitted extrinsics in this ruleset.',
];
