# Payment Flow Sequence Diagram

## 1. Gateway Availability Check

```mermaid
sequenceDiagram
    participant Client
    participant API as PaymentController
    participant Resolver as GatewayAvailabilityResolver
    participant DB as Database

    Client->>API: GET /api/v1/payments/gateways
    Note over Client,API: city_id=1, amount=100, module=booking
    
    API->>API: Validate request
    
    API->>Resolver: getAvailableGateways(context)
    
    Resolver->>DB: Query active gateways
    DB-->>Resolver: Return gateways with rules
    
    loop Each Gateway
        Resolver->>Resolver: Check city availability
        Resolver->>Resolver: Check module restriction
        Resolver->>Resolver: Check user status
        Resolver->>Resolver: Check amount limits
        Resolver->>Resolver: Check day restrictions
    end
    
    Resolver-->>API: Return filtered gateways
    
    API-->>Client: 200 OK - Available gateways
```

## 2. Payment Initiation Flow

```mermaid
sequenceDiagram
    participant Client
    participant API as PaymentController
    participant DB as Database
    participant Event as Event Dispatcher
    participant Manager as PaymentManager
    participant Driver as Gateway Driver
    participant Log as Payment Logger

    Client->>API: POST /api/v1/payments/initiate
    Note over Client,API: gateway_id, amount, payer, payable
    
    API->>API: Validate request
    
    API->>DB: Create Transaction
    Note over DB: Status: pending
    DB-->>API: Transaction created
    
    API->>Event: PaymentInitiated event
    Event->>Log: Log payment initiation
    Note over Log: storage/logs/payments.log
    
    API->>Manager: resolveByName(gateway)
    Manager-->>API: Return gateway driver
    
    alt Successful Payment
        API->>Driver: pay(transaction)
        Driver->>DB: Update transaction
        Note over DB: Status: paid
        Driver->>DB: Generate reference
        DB-->>Driver: Reference: MADA-xxx
        
        Driver-->>API: PaymentResponse (success)
        
        API->>Event: PaymentSucceeded event
        Event->>Log: Log successful payment
        
        API-->>Client: 200 OK - Payment successful
        Note over Client: transaction.reference, status=paid
        
    else Failed Payment
        API->>Driver: pay(transaction)
        Driver->>DB: Update transaction
        Note over DB: Status: failed
        Driver-->>API: PaymentResponse (failed)
        
        API->>Event: PaymentFailed event
        Event->>Log: Log payment failure with reason
        
        API-->>Client: 200 OK - Payment failed
        Note over Client: success=false, error message
        
    else Redirect Required (3D Secure)
        API->>Driver: pay(transaction)
        Driver-->>API: PaymentResponse (redirect)
        Note over API: action_url provided
        
        API-->>Client: 200 OK - Redirect URL
        Note over Client: Redirect to payment gateway
    end
```

## 3. Webhook Processing Flow

```mermaid
sequenceDiagram
    participant Gateway as Payment Gateway
    participant API as WebhookController
    participant Manager as PaymentManager
    participant Driver as Gateway Driver
    participant Event as Event Dispatcher
    participant Log as Payment Logger
    participant DB as Database

    Gateway->>API: POST /api/v1/payments/webhooks/{gateway}
    Note over Gateway,API: Webhook payload with signature
    
    API->>API: Log webhook receipt
    Note over Log: IP, user_agent, gateway name
    
    API->>Manager: resolveByName(gateway)
    Manager-->>API: Return gateway driver
    
    alt Webhook Not Supported
        API->>API: Check driver.hasWebhook()
        API-->>Gateway: 400 - Webhook not supported
        
    else Valid Webhook
        API->>Event: WebhookReceived event
        Event->>Log: Log webhook details
        
        API->>Driver: handleWebhook(request)
        
        Note over Driver: Verify signature (production)
        Note over Driver: Process webhook event
        Note over Driver: Update transaction status
        
        Driver->>DB: Update transaction
        Note over DB: Status based on webhook event
        
        Driver-->>API: PaymentResponse
        
        API-->>Gateway: 200 OK - Webhook processed
    end
```

## 4. Complete Payment Lifecycle (with States)

```mermaid
sequenceDiagram
    participant Client
    participant API as PaymentController
    participant Txn as Transaction
    participant Driver as Gateway Driver
    participant Event as Event Dispatcher

    Note over Txn: Initial State: pending
    
    Client->>API: POST /api/v1/payments/initiate
    
    API->>Txn: Create (status: pending)
    API->>Event: PaymentInitiated
    
    API->>Driver: pay(transaction)
    
    alt Direct Payment
        Driver->>Txn: transitionTo(Paid)
        Note over Txn: pending → paid
        Driver-->>API: Success
        
        API->>Event: PaymentSucceeded
        API-->>Client: Payment successful
        
    else Redirect Payment
        Driver-->>API: Redirect URL
        API-->>Client: Redirect to gateway
        
        Note over Client: User completes payment
        Client->>Driver: Return from gateway
        
        Driver->>Txn: transitionTo(Paid)
        Note over Txn: pending → paid
        Driver->>Event: PaymentSucceeded
    end
    
    Note over Txn: Optional: paid → completed
    Note over Txn: Or: any → failed (on error)
```

## Architecture Components

### Event-Driven Flow

```
PaymentInitiated → LogPaymentInitiated
    ↓
Payment Processing
    ↓
PaymentSucceeded → LogPaymentSucceeded
    OR
PaymentFailed → LogPaymentFailed
    ↓
PaymentCompleted → LogPaymentCompleted (optional)
```

### State Machine Transitions

```
[pending] ──→ [processing] ──→ [paid] ──→ [completed]
   │              │               │
   │              │               └──→ [failed]
   │              │
   │              └──→ [failed]
   │
   └──→ [failed]
```

### Gateway Availability Rules

```
Gateway Active?
    ↓ YES
City Match? (if restricted)
    ↓ YES
Module Match? (if restricted)
    ↓ YES
User Status Match? (if required)
    ↓ YES
Amount ≥ Minimum? (if set)
    ↓ YES
Day Allowed? (if restricted)
    ↓ YES
Available ✓
```

## Key Design Decisions

1. **Event-Driven Architecture**: Decouples payment processing from side effects (logging, notifications)
2. **Manager Pattern**: Easy to add new gateway drivers without modifying existing code
3. **State Machine**: Prevents invalid state transitions, clear lifecycle
4. **DTO Pattern**: Type-safe data transfer, self-documenting
5. **Dedicated Logging**: Separate payment logs for audit trail and debugging
6. **Webhook Support**: Async payment confirmation from gateways
7. **Config-Driven Rules**: Gateway availability based on database rules, not hardcoded
