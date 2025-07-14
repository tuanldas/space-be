// Để xem sơ đồ trực quan, hãy sao chép toàn bộ nội dung tệp này
// và dán vào trình soạn thảo tại trang web: https://dbdiagram.io/d
// --- Bảng chính ---
Table users {
  id uuid [pk, note: 'Primary Key']
  name varchar(255)
  email varchar(255) [unique, not null]
  email_verified_at timestamp
  password varchar(255)
  remember_token varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table wallets {
  id uuid [pk]
  user_id uuid [ref: > users.id]
  name varchar(255)
  balance "numeric(15,2)"
  currency varchar(255)
  created_by uuid [ref: > users.id, note: 'User who created the wallet']
  trashed_at timestamp
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table wallet_transactions {
  id uuid [pk]
  wallet_id uuid [ref: > wallets.id]
  category_id uuid [ref: > categories.id]
  created_by uuid [ref: > users.id]
  amount "numeric(11,2)"
  transaction_date "timestamp with time zone"
  transaction_type varchar(255)
  description text
  created_at timestamp
  updated_at timestamp
}

Table categories {
  id uuid [pk]
  name varchar(255)
  description text
  created_at timestamp
  updated_at timestamp
}

// --- Bảng phân quyền (Roles & Permissions) ---
Table roles {
  id uuid [pk]
  name varchar(255)
  slug varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table permissions {
  id uuid [pk]
  permission_group_id uuid [ref: > permission_groups.id]
  name varchar(255)
  slug varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table permission_groups {
  id uuid [pk]
  name varchar(255)
  slug varchar(255)
  description text
  created_at timestamp
  updated_at timestamp
}

// --- Bảng trung gian (Pivot Tables) ---
Table role_user {
  role_id uuid [ref: > roles.id]
  user_id uuid [ref: > users.id]

  Note: 'Many-to-Many relationship between users and roles'
}

Table permission_role {
  permission_id uuid [ref: > permissions.id]
  role_id uuid [ref: > roles.id]

  Note: 'Many-to-Many relationship between roles and permissions'
}


// --- Bảng đa hình (Polymorphic) ---
Table images {
  id uuid [pk]
  user_id uuid [ref: > users.id]
  disk varchar(255)
  path varchar(255)
  imageable_type varchar(255) [note: 'Polymorphic relation: Model name (e.g., User, Product)']
  imageable_id uuid [note: 'Polymorphic relation: Foreign key to the model']
  type varchar(255)
  created_at timestamp
  updated_at timestamp
}
