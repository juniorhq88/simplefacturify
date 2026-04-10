# API Documentation

## Architecture

The frontend communicates with a backend service via RESTful API endpoints. All requests require authentication using a Bearer token stored in localStorage under the key `miinbox_token`.

Base URL: `http://localhost:8000/api`

## Endpoints

### POST /threads/{threadId}/messages

Sends a message to a specific thread.

#### Request

```http
POST /api/threads/{threadId}/messages HTTP/1.1
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}

{
  "body": "Message content"
}
```

#### Request Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| threadId | string | The ID of the thread to send the message to |
| body | string | The message content |

#### Response

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
  "success": true
}
```

#### Example

```javascript
fetch(`http://localhost:8000/api/threads/conversation-123/messages`, {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "Authorization": `Bearer ${token}`
  },
  body: JSON.stringify({ body: "Hello, how are you?" })
});
```

## Technical Decisions

- Authentication uses Bearer tokens stored in localStorage
- All API endpoints are prefixed with `/api`
- JSON is used for request and response payloads
- The frontend is built with Next.js and uses React hooks for API interactions
- Error handling is implemented at the hook level
- API base URL is centralized in constants for easy configuration