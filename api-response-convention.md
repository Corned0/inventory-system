# API Response Convention

## 1. Purpose

This document defines the standard JSON response structure and HTTP status codes for the Inventory API.

The goal is to keep API responses predictable and consistent across all modules so that frontend clients, integrations, and future developers can rely on the same contract.

---

## 2. General Principles

- Use standard HTTP status codes.
- Return JSON for all `/api/*` endpoints.
- Use `data` for successful resource payloads.
- Use `message` for human-readable status or error information.
- Use `errors` for validation or field-specific business errors.
- Use Laravel API Resources for successful resource responses.
- Do not expose exception classes, stack traces, file paths, SQL queries, or other internal implementation details in production.
- Do not create custom response wrappers unless there is a concrete requirement that Laravel's existing mechanisms cannot satisfy.

---

# 3. Success Responses

## 3.1 Single Resource

HTTP status:

```text
200 OK
```

Example:

```json
{
    "data": {
        "id": 1,
        "name": "Dell OptiPlex 7090",
        "item_type_id": 1,
        "category_id": 5
    }
}
```

Laravel implementation should normally use an API Resource:

```php
return new ItemResource($item);
```

---

## 3.2 Created Resource

HTTP status:

```text
201 Created
```

Example:

```json
{
    "data": {
        "id": 1,
        "name": "Dell OptiPlex 7090"
    },
    "message": "Item created successfully."
}
```

Use `201 Created` when a new resource has been successfully created.

---

## 3.3 Successful Update

HTTP status:

```text
200 OK
```

Example:

```json
{
    "data": {
        "id": 1,
        "name": "Dell OptiPlex 7090"
    },
    "message": "Item updated successfully."
}
```

---

## 3.4 Successful Delete

Use:

```text
204 No Content
```

when the endpoint does not need to return a response body.

Example:

```http
DELETE /api/items/1
```

Response:

```text
204 No Content
```

Do not return JSON with a body when using `204`.

If the API needs to return a confirmation message, use `200 OK` instead:

```json
{
    "message": "Item deleted successfully."
}
```

---

# 4. Collection Responses

## 4.1 Non-Paginated Collection

HTTP status:

```text
200 OK
```

Example:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Dell OptiPlex 7090"
        },
        {
            "id": 2,
            "name": "HP ProDesk 600"
        }
    ]
}
```

Laravel implementation:

```php
return ItemResource::collection($items);
```

---

## 4.2 Paginated Collection

For large datasets, use Laravel pagination.

Example:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Dell OptiPlex 7090"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 20,
        "to": 20,
        "total": 200
    },
    "links": {
        "first": "/api/items?page=1",
        "last": "/api/items?page=10",
        "prev": null,
        "next": "/api/items?page=2"
    }
}
```

Use pagination for endpoints that can potentially return large datasets, such as:

- Items
- Inventory transactions
- Receiving records
- Issuances
- Transfers
- Assets
- Audit logs

---

# 5. Validation Errors

HTTP status:

```text
422 Unprocessable Entity
```

Validation should be handled through Laravel Form Requests.

Example:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "item_type_id": [
            "The selected item type is invalid."
        ]
    }
}
```

Do not put validation errors inside `data`.

Example request:

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'item_type_id' => ['required', 'integer', 'exists:item_types,id'],
    ];
}
```

---

# 6. Authentication Errors

## 6.1 Unauthenticated

HTTP status:

```text
401 Unauthorized
```

Example:

```json
{
    "message": "Unauthenticated."
}
```

Use when:

- No token was provided.
- The token is invalid.
- The token is expired or otherwise unusable.

---

# 7. Authorization Errors

HTTP status:

```text
403 Forbidden
```

Example:

```json
{
    "message": "You do not have permission to perform this action."
}
```

Use when the user is authenticated but does not have the required permission.

Example:

```text
Authenticated user
        ↓
Permission check
        ↓
Permission denied
        ↓
403 Forbidden
```

---

# 8. Not Found Errors

HTTP status:

```text
404 Not Found
```

Example:

```json
{
    "message": "Item not found."
}
```

Use when the requested resource does not exist.

Do not return:

```json
{
    "data": null
}
```

for a resource that does not exist.

---

# 9. Business Rule Errors

HTTP status:

```text
409 Conflict
```

Use `409` when the request is structurally valid but cannot be completed because it conflicts with the current business state.

Examples:

- Insufficient stock.
- Duplicate serial number.
- Item is already disposed.
- Warehouse is inactive.
- Receiving transaction is already completed.
- Asset is already assigned.
- Stock cannot be transferred from the specified location.

Example:

```json
{
    "message": "Insufficient stock.",
    "errors": {
        "quantity": [
            "Only 5 units are available."
        ]
    }
}
```

Business exceptions should be handled centrally rather than using `try/catch` blocks in every controller.

---

# 10. External Service Errors

The Inventory API may communicate with external services such as HRMIS.

If a required external dependency is temporarily unavailable:

HTTP status:

```text
503 Service Unavailable
```

Example:

```json
{
    "message": "The HRMIS service is currently unavailable."
}
```

Do not expose:

- Internal URLs.
- Authentication credentials.
- Stack traces.
- HTTP client internals.
- SQL or connection details.

---

# 11. Rate Limiting

HTTP status:

```text
429 Too Many Requests
```

Example:

```json
{
    "message": "Too many requests. Please try again later."
}
```

Laravel may provide additional rate-limit headers automatically.

---

# 12. Server Errors

HTTP status:

```text
500 Internal Server Error
```

Production response:

```json
{
    "message": "An unexpected error occurred."
}
```

Never expose internal exception details in production.

Do not return:

```json
{
    "exception": "...",
    "file": "...",
    "line": 123,
    "trace": []
}
```

These details can expose implementation information and sensitive application data.

---

# 13. HTTP Status Code Reference

| Status | Meaning | Typical Inventory API Use |
|---:|---|---|
| 200 | OK | Successful read/update |
| 201 | Created | Resource successfully created |
| 204 | No Content | Successful delete/no response body |
| 400 | Bad Request | Malformed request |
| 401 | Unauthorized | Authentication required/failed |
| 403 | Forbidden | Permission denied |
| 404 | Not Found | Resource does not exist |
| 409 | Conflict | Business/state conflict |
| 422 | Unprocessable Entity | Validation failure |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Unexpected server failure |
| 503 | Service Unavailable | Required external service unavailable |

---

# 14. Response Shape Summary

## Success

```json
{
    "data": {}
}
```

## Success with message

```json
{
    "data": {},
    "message": "Operation completed successfully."
}
```

## Collection

```json
{
    "data": []
}
```

## Paginated collection

```json
{
    "data": [],
    "meta": {},
    "links": {}
}
```

## Validation error

```json
{
    "message": "The given data was invalid.",
    "errors": {}
}
```

## Authentication/authorization error

```json
{
    "message": "Unauthenticated."
}
```

or:

```json
{
    "message": "You do not have permission to perform this action."
}
```

## Business error

```json
{
    "message": "Insufficient stock.",
    "errors": {}
}
```

## Server/dependency error

```json
{
    "message": "An unexpected error occurred."
}
```

---

# 15. Laravel Implementation Rules

## API Resources

Use Laravel API Resources for successful resource responses:

```bash
php artisan make:resource ItemResource
```

Example:

```php
public function show(Item $item): ItemResource
{
    return new ItemResource($item);
}
```

For collections:

```php
public function index(): AnonymousResourceCollection
{
    return ItemResource::collection($items);
}
```

---

## Form Requests

Use Form Requests for request validation:

```bash
php artisan make:request StoreItemRequest
```

Do not manually validate inside every controller unless there is a specific reason.

---

## Global Exception Handling

API exception rendering is configured centrally in:

```text
bootstrap/app.php
```

The API should render JSON responses for `/api/*` requests.

Business exceptions belong in:

```text
app/Exceptions/
```

Examples:

```text
InsufficientStockException.php
DuplicateSerialNumberException.php
InvalidInventoryStateException.php
ExternalServiceUnavailableException.php
```

---

# 16. Controller Guidelines

Controllers should remain thin.

Prefer:

```php
public function show(Item $item): ItemResource
{
    return new ItemResource($item);
}
```

or:

```php
public function store(StoreItemRequest $request): JsonResponse
{
    // Delegate the business operation.
}
```

Avoid putting complex business rules directly in controllers.

Avoid:

```php
try {
    // Large business operation...
} catch (...) {
    // Manually format every exception...
}
```

Business operations should be delegated to domain actions/services, while centralized exception handling determines the API error format.

---

# 17. Inventory-Specific Examples

### Receive stock

```http
POST /api/receivings
```

Success:

```http
201 Created
```

```json
{
    "data": {
        "id": 1001,
        "reference_no": "RCV-2026-000001",
        "status": "completed"
    },
    "message": "Stock received successfully."
}
```

### Issue stock with insufficient quantity

```http
POST /api/issuances
```

Response:

```http
409 Conflict
```

```json
{
    "message": "Insufficient stock.",
    "errors": {
        "quantity": [
            "Only 5 units are available."
        ]
    }
}
```

### Request invalid item

```http
POST /api/issuances
```

Response:

```http
422 Unprocessable Entity
```

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "item_id": [
            "The selected item is invalid."
        ]
    }
}
```

The distinction is important:

```text
Invalid input
    → 422

Valid input but impossible business operation
    → 409
```

---

# 18. API Versioning

For the initial implementation, use:

```text
/api/v1/
```

Example:

```text
POST /api/v1/auth/login
GET  /api/v1/items
POST /api/v1/items
GET  /api/v1/items/{item}
```

This gives the API a stable version boundary if the contract needs to change later.

When adding versioning, the route structure should be:

```text
routes/
├── api.php
└── api/
    └── v1/
        ├── auth.php
        ├── items.php
        ├── inventory.php
        └── assets.php
```

Do not introduce multiple API versions until there is an actual need, but establish the `/v1` boundary early if this API will be consumed by multiple clients or services.

---

# 19. Checklist

Before implementing new modules, verify:

- [ ] Successful resources use `data`.
- [ ] Collections use `data` arrays.
- [ ] Large collections are paginated.
- [ ] Validation uses Form Requests.
- [ ] Validation returns `422`.
- [ ] Unauthenticated requests return `401`.
- [ ] Forbidden requests return `403`.
- [ ] Missing resources return `404`.
- [ ] Business conflicts return `409`.
- [ ] External dependency failures return `503`.
- [ ] Unexpected server errors return `500`.
- [ ] Production errors do not expose stack traces.
- [ ] API Resources format successful resources.
- [ ] Controllers remain thin.
- [ ] Business exceptions are handled centrally.
