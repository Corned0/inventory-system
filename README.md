# inventory-system

1. PostgreSQL connection -
2. Sanctum/API authentication -
3. API response convention -
4. Exception handling -
5. User/permission integration -
6. HRMIS integration client
7. Units -
8. Categories -
9. Item Types
10. Items

/api/v1
│
├── auth
│ ├── POST /login
│ └── POST /logout
│
├── users
│ ├── GET /
│ ├── GET /{user}
│ ├── POST /
│ ├── PATCH /{user}
│ └── ...
│
├── roles
│ ├── GET /
│ ├── POST /
│ ├── PATCH /{role}
│ └── ...
│
├── permissions
│ └── GET /
│
└── inventory
└── ...
