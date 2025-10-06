# API Documentation

## Authentication

All protected endpoints require Bearer token authentication.

### Login
\`\`\`http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
\`\`\`

Response:
\`\`\`json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": {
      "name": "admin",
      "display_name": "Administrator"
    }
  },
  "token": "1|abc123..."
}
\`\`\`

### Logout
\`\`\`http
POST /api/logout
Authorization: Bearer {token}
\`\`\`

## Routers

### List Routers
\`\`\`http
GET /api/routers?page=1&per_page=15&category_id=1&status=up&search=router1
Authorization: Bearer {token}
\`\`\`

Query Parameters:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `category_id` - Filter by category
- `status` - Filter by status (up/down/unknown)
- `is_active` - Filter by active status (true/false)
- `search` - Search by name, IP, or location

### Create Router
\`\`\`http
POST /api/routers
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Router 1",
  "ip_address": "192.168.1.1",
  "port": 8728,
  "username": "admin",
  "password": "password",
  "category_id": 1,
  "latitude": -6.200000,
  "longitude": 106.816666,
  "location": "Jakarta",
  "contact_person": "John Doe",
  "contact_phone": "+62123456789",
  "is_active": true
}
\`\`\`

### Update Router
\`\`\`http
PUT /api/routers/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Router Name",
  "is_active": false
}
\`\`\`

### Delete Router
\`\`\`http
DELETE /api/routers/{id}
Authorization: Bearer {token}
\`\`\`

## Router Status

### Check Single Router
\`\`\`http
GET /api/routers/{id}/check
Authorization: Bearer {token}
\`\`\`

Response:
\`\`\`json
{
  "router_id": 1,
  "status": "up",
  "message": null,
  "metrics": {
    "uptime": "1w2d3h",
    "cpu_load": "5",
    "free_memory": "512000000",
    "total_memory": "1073741824"
  },
  "checked_at": "2024-01-01T12:00:00Z"
}
\`\`\`

### Check All Routers
\`\`\`http
POST /api/routers/check-all
Authorization: Bearer {token}
\`\`\`

### Get Router History
\`\`\`http
GET /api/routers/{id}/history?from=2024-01-01&to=2024-01-31&per_page=50
Authorization: Bearer {token}
\`\`\`

## Categories

### List Categories
\`\`\`http
GET /api/categories
Authorization: Bearer {token}
\`\`\`

### Create Category
\`\`\`http
POST /api/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Core Router",
  "description": "Main backbone routers",
  "color": "#ef4444"
}
\`\`\`

## Permissions

### Router Permissions
- `routers.view` - View routers
- `routers.create` - Create routers
- `routers.edit` - Edit routers
- `routers.delete` - Delete routers

### Monitoring Permissions
- `monitoring.view` - View monitoring data
- `monitoring.export` - Export monitoring data

### Category Permissions
- `categories.manage` - Manage categories

### User Permissions
- `users.view` - View users
- `users.create` - Create users
- `users.edit` - Edit users
- `users.delete` - Delete users
