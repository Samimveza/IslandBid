create table tenant (
    id_tenant varchar(450) primary key,
    tenant_name varchar(255) not null,
    is_deactivated boolean not null default false,
    date_created_utc timestamp(3) not null default now()
);

create table app_user (
    id_user varchar(450) primary key,
    first_name varchar(150) not null,
    last_name varchar(150) not null,
    email varchar(255) not null,
    phone varchar(50),
    password_hash text not null,
    is_email_verified boolean not null default false,
    is_phone_verified boolean not null default false,
    is_deactivated boolean not null default false,
    id_tenant varchar(450),
    date_created_utc timestamp(3) not null default now(),
    date_updated_utc timestamp(3),
    constraint uq_app_user_email unique (email),
    constraint fk_app_user_tenant
        foreign key (id_tenant) references tenant(id_tenant)
);

create table category (
    id_category varchar(450) primary key,
    category_name varchar(150) not null,
    category_code varchar(100) not null,
    description text,
    is_active boolean not null default true,
    display_order bigint,
    date_created_utc timestamp(3) not null default now(),
    constraint uq_category_code unique (category_code)
);

create table category_field (
    id_category_field varchar(450) primary key,
    id_category varchar(450) not null,
    field_name varchar(150) not null,
    field_label varchar(150) not null,
    field_type varchar(50) not null,
    is_required boolean not null default false,
    is_filterable boolean not null default false,
    is_active boolean not null default true,
    display_order bigint,
    date_created_utc timestamp(3) not null default now(),
    constraint fk_category_field_category
        foreign key (id_category) references category(id_category),
    constraint ck_category_field_type
        check (field_type in ('text', 'number', 'decimal', 'date', 'boolean', 'select'))
);

create table category_field_option (
    id_category_field_option varchar(450) primary key,
    id_category_field varchar(450) not null,
    option_value varchar(255) not null,
    option_label varchar(255) not null,
    display_order bigint,
    is_active boolean not null default true,
    constraint fk_category_field_option_field
        foreign key (id_category_field) references category_field(id_category_field)
);

create table item (
    id_item varchar(450) primary key,
    id_user varchar(450) not null,
    id_category varchar(450) not null,
    title varchar(255) not null,
    short_description varchar(500),
    description text not null,
    listing_type varchar(50) not null,
    item_status varchar(50) not null default 'draft',
    start_price numeric(18,2),
    fixed_price numeric(18,2),
    current_highest_bid numeric(18,2),
    bid_start_utc timestamp(3),
    bid_end_utc timestamp(3),
    currency_code varchar(20) not null default 'MUR',
    location_text varchar(255),
    seo_slug varchar(500) not null,
    meta_title varchar(255),
    meta_description varchar(500),
    view_count bigint not null default 0,
    is_published boolean not null default false,
    is_active boolean not null default true,
    date_created_utc timestamp(3) not null default now(),
    date_updated_utc timestamp(3),
    date_published_utc timestamp(3),
    constraint fk_item_user
        foreign key (id_user) references app_user(id_user),
    constraint fk_item_category
        foreign key (id_category) references category(id_category),
    constraint uq_item_seo_slug unique (seo_slug),
    constraint ck_item_listing_type
        check (listing_type in ('bid', 'fixed_price', 'both')),
    constraint ck_item_status
        check (item_status in ('draft', 'active', 'sold', 'expired', 'cancelled')),
    constraint ck_item_prices_non_negative
        check (
            (start_price is null or start_price >= 0)
            and (fixed_price is null or fixed_price >= 0)
            and (current_highest_bid is null or current_highest_bid >= 0)
        )
);


create table item_field_value (
    id_item_field_value varchar(450) primary key,
    id_item varchar(450) not null,
    id_category_field varchar(450) not null,
    field_value_text text,
    field_value_number numeric(18,2),
    field_value_boolean boolean,
    field_value_date date,
    field_value_option varchar(255),
    date_created_utc timestamp(3) not null default now(),
    constraint fk_item_field_value_item
        foreign key (id_item) references item(id_item) on delete cascade,
    constraint fk_item_field_value_category_field
        foreign key (id_category_field) references category_field(id_category_field)
);

create table bid (
    id_bid varchar(450) primary key,
    id_item varchar(450) not null,
    id_user varchar(450) not null,
    bid_amount numeric(18,2) not null,
    bid_status varchar(50) not null default 'active',
    bid_time_utc timestamp(3) not null default now(),
    date_updated_utc timestamp(3),
    constraint fk_bid_item
        foreign key (id_item) references item(id_item) on delete cascade,
    constraint fk_bid_user
        foreign key (id_user) references app_user(id_user),
    constraint ck_bid_status
        check (bid_status in ('active', 'updated', 'removed', 'won', 'lost')),
    constraint ck_bid_amount_positive
        check (bid_amount > 0)
);

create table saved_item (
    id_saved_item varchar(450) primary key,
    id_user varchar(450) not null,
    id_item varchar(450) not null,
    date_created_utc timestamp(3) not null default now(),
    constraint fk_saved_item_user
        foreign key (id_user) references app_user(id_user) on delete cascade,
    constraint fk_saved_item_item
        foreign key (id_item) references item(id_item) on delete cascade,
    constraint uq_saved_item unique (id_user, id_item)
);

create table document_type (
    id_document_type varchar(450) primary key,
    document_type_name varchar(150) not null,
    document_type_code varchar(100) not null,
    is_deactivated boolean not null default false,
    date_created_utc timestamp(3) not null default now(),
    constraint uq_document_type_code unique (document_type_code)
);

create table parameter (
    id_parameter varchar(450) primary key,
    paramater_value text,
    is_deactivated boolean not null default false,
    code varchar(450),
    id_tenant varchar(450),
    constraint fk_parameter_tenant
        foreign key (id_tenant) references tenant(id_tenant)
);

create table document (
    id_document varchar(450) primary key,
    file_name varchar(450),
    file_extension varchar(450),
    document_order bigint,
    is_deactivated boolean not null default false,
    id_document_type varchar(450),
    physical_file_path varchar(450),
    id_parameter_base_physical_file_path varchar(450),
    server_file_path varchar(450),
    id_parameter_base_server_url varchar(450),
    id_tenant varchar(450),
    date_created_utc timestamp(3) not null default now(),
    constraint fk_document_document_type
        foreign key (id_document_type) references document_type(id_document_type),
    constraint fk_document_parameter_physical_path
        foreign key (id_parameter_base_physical_file_path) references parameter(id_parameter),
    constraint fk_document_parameter_server_url
        foreign key (id_parameter_base_server_url) references parameter(id_parameter),
    constraint fk_document_tenant
        foreign key (id_tenant) references tenant(id_tenant)
);

create table item_document (
    id_item_document varchar(450) primary key,
    id_item varchar(450) not null,
    id_document varchar(450) not null,
    display_order bigint,
    is_primary boolean not null default false,
    date_created_utc timestamp(3) not null default now(),
    constraint fk_item_document_item
        foreign key (id_item) references item(id_item) on delete cascade,
    constraint fk_item_document_document
        foreign key (id_document) references document(id_document) on delete cascade,
    constraint uq_item_document unique (id_item, id_document)
);

create index ix_app_user_email on app_user(email);

create index ix_item_user on item(id_user);
create index ix_item_category on item(id_category);
create index ix_item_status on item(item_status);
create index ix_item_listing_type on item(listing_type);
create index ix_item_bid_end_utc on item(bid_end_utc);
create index ix_item_is_published on item(is_published);
create index ix_item_seo_slug on item(seo_slug);

create index ix_bid_item on bid(id_item);
create index ix_bid_user on bid(id_user);
create index ix_bid_item_bid_time on bid(id_item, bid_time_utc desc);
create index ix_bid_item_bid_status on bid(id_item, bid_status);

create index ix_saved_item_user on saved_item(id_user);
create index ix_item_document_item on item_document(id_item);
create index ix_document_type on document(id_document_type);



insert into tenant (id_tenant, tenant_name)
values ('TENANT_DEFAULT', 'Default Tenant');

insert into category (id_category, category_name, category_code, display_order)
values
('CAT_CAR', 'Cars', 'CAR', 1),
('CAT_REAL_ESTATE', 'Real Estate', 'REAL_ESTATE', 2),
('CAT_ELECTRONICS', 'Electronics', 'ELECTRONICS', 3),
('CAT_MOBILE', 'Mobile Phones', 'MOBILE', 4),
('CAT_COMPUTER', 'Computers', 'COMPUTER', 5),
('CAT_FURNITURE', 'Furniture', 'FURNITURE', 6),
('CAT_APPLIANCE', 'Appliances', 'APPLIANCE', 7),
('CAT_JEWELLERY', 'Jewellery', 'JEWELLERY', 8),
('CAT_MOTORCYCLE', 'Motorcycles', 'MOTORCYCLE', 9),
('CAT_SERVICE', 'Services', 'SERVICE', 10),
('CAT_OTHER', 'Other', 'OTHER', 11);


insert into category_field values
('CF_CAR_MAKE', 'CAT_CAR', 'make', 'Make', 'text', true, true, true, 1, now()),
('CF_CAR_MODEL', 'CAT_CAR', 'model', 'Model', 'text', true, true, true, 2, now()),
('CF_CAR_YEAR', 'CAT_CAR', 'year', 'Year', 'number', true, true, true, 3, now()),
('CF_CAR_MILEAGE', 'CAT_CAR', 'mileage', 'Mileage (km)', 'number', true, true, true, 4, now()),
('CF_CAR_TRANSMISSION', 'CAT_CAR', 'transmission', 'Transmission', 'select', false, true, true, 5, now()),
('CF_CAR_FUEL', 'CAT_CAR', 'fuel_type', 'Fuel Type', 'select', false, true, true, 6, now()),
('CF_CAR_COLOR', 'CAT_CAR', 'color', 'Color', 'text', false, false, true, 7, now()),
('CF_CAR_CONDITION', 'CAT_CAR', 'condition', 'Condition', 'select', true, true, true, 8, now());


insert into category_field values
('CF_RE_PROPERTY_TYPE', 'CAT_REAL_ESTATE', 'property_type', 'Property Type', 'select', true, true, true, 1, now()),
('CF_RE_PLOT_SIZE', 'CAT_REAL_ESTATE', 'plot_size', 'Plot Size (m²)', 'number', true, true, true, 2, now()),
('CF_RE_LOCATION', 'CAT_REAL_ESTATE', 'location', 'Location', 'text', true, true, true, 3, now()),
('CF_RE_BEDROOMS', 'CAT_REAL_ESTATE', 'bedrooms', 'Bedrooms', 'number', false, true, true, 4, now()),
('CF_RE_BATHROOMS', 'CAT_REAL_ESTATE', 'bathrooms', 'Bathrooms', 'number', false, true, true, 5, now()),
('CF_RE_FURNISHED', 'CAT_REAL_ESTATE', 'furnished', 'Furnished', 'select', false, true, true, 6, now()),
('CF_RE_CONDITION', 'CAT_REAL_ESTATE', 'condition', 'Condition', 'select', false, true, true, 7, now());


insert into category_field values
('CF_EL_BRAND', 'CAT_ELECTRONICS', 'brand', 'Brand', 'text', true, true, true, 1, now()),
('CF_EL_MODEL', 'CAT_ELECTRONICS', 'model', 'Model', 'text', true, true, true, 2, now()),
('CF_EL_CONDITION', 'CAT_ELECTRONICS', 'condition', 'Condition', 'select', true, true, true, 3, now()),
('CF_EL_WARRANTY', 'CAT_ELECTRONICS', 'warranty', 'Warranty Available', 'boolean', false, true, true, 4, now());


insert into category_field values
('CF_SER_TYPE', 'CAT_SERVICE', 'service_type', 'Service Type', 'text', true, true, true, 1, now()),
('CF_SER_AREA', 'CAT_SERVICE', 'area', 'Service Area', 'text', true, true, true, 2, now()),
('CF_SER_DURATION', 'CAT_SERVICE', 'duration', 'Duration', 'text', false, false, true, 3, now());


insert into category_field values
('CF_OTH_BRAND', 'CAT_OTHER', 'brand', 'Brand', 'text', false, true, true, 1, now()),
('CF_OTH_MODEL', 'CAT_OTHER', 'model', 'Model', 'text', false, true, true, 2, now()),
('CF_OTH_CONDITION', 'CAT_OTHER', 'condition', 'Condition', 'select', false, true, true, 3, now());


insert into category_field_option values
('OPT_COND_NEW', 'CF_CAR_CONDITION', 'NEW', 'New', 1, true),
('OPT_COND_USED', 'CF_CAR_CONDITION', 'USED', 'Used', 2, true),
('OPT_COND_REFURB', 'CF_CAR_CONDITION', 'REFURB', 'Refurbished', 3, true);


insert into category_field_option values
('OPT_TR_AUTO', 'CF_CAR_TRANSMISSION', 'AUTO', 'Automatic', 1, true),
('OPT_TR_MANUAL', 'CF_CAR_TRANSMISSION', 'MANUAL', 'Manual', 2, true);

insert into category_field_option values
('OPT_FUEL_PETROL', 'CF_CAR_FUEL', 'PETROL', 'Petrol', 1, true),
('OPT_FUEL_DIESEL', 'CF_CAR_FUEL', 'DIESEL', 'Diesel', 2, true),
('OPT_FUEL_HYBRID', 'CF_CAR_FUEL', 'HYBRID', 'Hybrid', 3, true),
('OPT_FUEL_ELECTRIC', 'CF_CAR_FUEL', 'ELECTRIC', 'Electric', 4, true);


insert into category_field_option values
('OPT_PROP_HOUSE', 'CF_RE_PROPERTY_TYPE', 'HOUSE', 'House', 1, true),
('OPT_PROP_APT', 'CF_RE_PROPERTY_TYPE', 'APARTMENT', 'Apartment', 2, true),
('OPT_PROP_LAND', 'CF_RE_PROPERTY_TYPE', 'LAND', 'Land', 3, true),
('OPT_PROP_COMM', 'CF_RE_PROPERTY_TYPE', 'COMMERCIAL', 'Commercial', 4, true);


insert into document_type (id_document_type, document_type_name, document_type_code)
values ('DOC_TYPE_ITEM_IMAGE', 'Item Image', 'ITEM_IMAGE');


insert into parameter (id_parameter, code, paramater_value, id_tenant)
values
('PARAM_PHYSICAL_PATH', 'BASE_PHYSICAL_PATH', '/var/www/uploads/', 'TENANT_DEFAULT'),
('PARAM_SERVER_URL', 'BASE_SERVER_URL', 'https://yourdomain.com/uploads/', 'TENANT_DEFAULT');