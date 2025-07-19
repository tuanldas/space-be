// Để xem sơ đồ trực quan, hãy sao chép toàn bộ nội dung tệp này
// và dán vào trình soạn thảo tại trang web: https://dbdiagram.io/d

// --- Bảng người dùng và xác thực ---
Table users {
  id bigint [pk, increment]
  name varchar(255)
  email varchar(255) [unique, not null]
  email_verified_at timestamp
  password varchar(255)
  remember_token varchar(100)
  created_at timestamp
  updated_at timestamp
}

Table password_reset_tokens {
  email varchar(255) [pk]
  token varchar(255)
  created_at timestamp
}

Table sessions {
  id varchar(255) [pk]
  user_id bigint [ref: > users.id]
  ip_address varchar(45)
  user_agent text
  payload longtext
  last_activity integer
}

// --- Bảng OAuth (Laravel Passport) ---
Table oauth_clients {
  id uuid [pk]
  user_id bigint [ref: > users.id]
  name varchar(255)
  secret varchar(100)
  provider varchar(255)
  redirect text
  personal_access_client boolean
  password_client boolean
  revoked boolean
  created_at timestamp
  updated_at timestamp
}

Table oauth_access_tokens {
  id char(80) [pk]
  user_id bigint [ref: > users.id]
  client_id uuid [ref: > oauth_clients.id]
  name varchar(255)
  scopes text
  revoked boolean
  created_at timestamp
  updated_at timestamp
  expires_at timestamp
}

Table oauth_auth_codes {
  id varchar(100) [pk]
  user_id bigint [ref: > users.id]
  client_id uuid [ref: > oauth_clients.id]
  scopes text
  revoked boolean
  expires_at timestamp
}

Table oauth_refresh_tokens {
  id varchar(100) [pk]
  access_token_id varchar(100) [ref: > oauth_access_tokens.id]
  revoked boolean
  expires_at timestamp
}

// --- Bảng phân quyền (Bouncer) ---
Table abilities {
  id bigint [pk, increment]
  name varchar(255)
  title varchar(255)
  entity_id bigint
  entity_type varchar(255)
  only_owned boolean
  options json
  scope integer
  created_at timestamp
  updated_at timestamp
}

Table roles {
  id bigint [pk, increment]
  name varchar(255)
  title varchar(255)
  scope integer
  created_at timestamp
  updated_at timestamp

  indexes {
    (name, scope) [unique, name: 'roles_name_unique']
  }
}

Table assigned_roles {
  id bigint [pk, increment]
  role_id bigint [ref: > roles.id]
  entity_id bigint
  entity_type varchar(255)
  restricted_to_id bigint
  restricted_to_type varchar(255)
  scope integer

  indexes {
    (entity_id, entity_type, scope) [name: 'assigned_roles_entity_index']
  }
}

Table permissions {
  id bigint [pk, increment]
  ability_id bigint [ref: > abilities.id]
  entity_id bigint
  entity_type varchar(255)
  forbidden boolean
  scope integer

  indexes {
    (entity_id, entity_type, scope) [name: 'permissions_entity_index']
  }
}

// --- Bảng mới sắp triển khai ---
Table wallets {
  id uuid [pk]
  user_id bigint [ref: > users.id]
  name varchar(255)
  balance "numeric(15,2)"
  currency varchar(255)
  created_by bigint [ref: > users.id]
  trashed_at timestamp
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table wallet_transactions {
  id uuid [pk]
  wallet_id uuid [ref: > wallets.id]
  category_id uuid [ref: > transaction_categories.id]
  created_by bigint [ref: > users.id]
  amount "numeric(11,2)"
  transaction_date "timestamp with time zone"
  transaction_type varchar(255)
  description text
  created_at timestamp
  updated_at timestamp
}

Table transaction_categories {
  id uuid [pk]
  name varchar(255)
  description text
  type varchar(50) [note: 'income/expense/transfer']
  user_id bigint [ref: > users.id, note: 'null means system default category']
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

// --- Bảng đa hình (Polymorphic) ---
Table images {
  id uuid [pk]
  user_id bigint [ref: > users.id]
  disk varchar(255)
  path varchar(255)
  imageable_type varchar(255) [note: 'Polymorphic relation: Model name (e.g., User, Product)']
  imageable_id varchar(255) [note: 'Polymorphic relation: Foreign key to the model']
  type varchar(255)
  created_at timestamp
  updated_at timestamp
}
