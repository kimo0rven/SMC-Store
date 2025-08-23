-- Disable foreign key checks temporarily to allow table creation in any order
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `user_account`
-- -----------------------------------------------------
CREATE TABLE user_account (
    user_account_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    account_status ENUM('active', 'inactive') NOT NULL, -- Updated
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_account_id INT NOT NULL UNIQUE,
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    mobile VARCHAR(20),
    gender VARCHAR(10),
    dob DATE,
    civil_status ENUM('single', 'married'), -- Updated
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `shipping_address`
-- -----------------------------------------------------
CREATE TABLE shipping_address (
    shipping_address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    street_address VARCHAR(255),
    barangay VARCHAR(100),
    city VARCHAR(100),
    province VARCHAR(100),
    type ENUM('home', 'work', 'pick up'), -- Updated
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `category`
-- -----------------------------------------------------
CREATE TABLE category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `listings`
-- -----------------------------------------------------
CREATE TABLE listings (
    listings_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_owner_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL,
    image_url VARCHAR(255),
    category_id INT NOT NULL,
    listing_status ENUM('active', 'draft', 'pending', 'unavailable', 'inactive', 'out of stock', 'pre-order', 'discontinued') NOT NULL, -- Updated
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `order`
-- -----------------------------------------------------
CREATE TABLE `order` (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'on hold', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') NOT NULL, -- Updated
    shipping_address_id INT NOT NULL,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `order_items`
-- -----------------------------------------------------
CREATE TABLE order_items (
    order_id INT NOT NULL,
    listing_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    item_total DECIMAL(10, 2) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (order_id, listing_id)
);

-- -----------------------------------------------------
-- Table `payment`
-- -----------------------------------------------------
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method ENUM('cod', 'paypal'), -- Updated
    transaction_id VARCHAR(255),
    payment_status VARCHAR(50) NOT NULL,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `review`
-- -----------------------------------------------------
CREATE TABLE review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    review_content TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table `review_image`
-- -----------------------------------------------------
CREATE TABLE review_image (
    review_image_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    image_file VARCHAR(255),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Adding Foreign Key Constraints
-- -----------------------------------------------------

-- user_account to role
ALTER TABLE user_account
ADD CONSTRAINT fk_user_account_role
FOREIGN KEY (role_id) REFERENCES role(role_id);

-- user to user_account
ALTER TABLE user
ADD CONSTRAINT fk_user_user_account
FOREIGN KEY (user_account_id) REFERENCES user_account(user_account_id);

-- shipping_address to user
ALTER TABLE shipping_address
ADD CONSTRAINT fk_shipping_address_user
FOREIGN KEY (user_id) REFERENCES user(user_id);

-- listings to user (as owner/seller)
ALTER TABLE listings
ADD CONSTRAINT fk_listings_owner
FOREIGN KEY (listing_owner_id) REFERENCES user(user_id);

-- listings to category
ALTER TABLE listings
ADD CONSTRAINT fk_listings_category
FOREIGN KEY (category_id) REFERENCES category(category_id);

-- order to user (as buyer)
ALTER TABLE `order`
ADD CONSTRAINT fk_order_user
FOREIGN KEY (user_id) REFERENCES user(user_id);

-- order to shipping_address
ALTER TABLE `order`
ADD CONSTRAINT fk_order_shipping_address
FOREIGN KEY (shipping_address_id) REFERENCES shipping_address(shipping_address_id);

-- order_items to order
ALTER TABLE order_items
ADD CONSTRAINT fk_order_items_order
FOREIGN KEY (order_id) REFERENCES `order`(order_id);

-- order_items to listings
ALTER TABLE order_items
ADD CONSTRAINT fk_order_items_listings
FOREIGN KEY (listing_id) REFERENCES listings(listings_id);

-- payment to order
ALTER TABLE payment
ADD CONSTRAINT fk_payment_order
FOREIGN KEY (order_id) REFERENCES `order`(order_id);

-- review to listings
ALTER TABLE review
ADD CONSTRAINT fk_review_listings
FOREIGN KEY (listing_id) REFERENCES listings(listings_id);

-- review_image to review
ALTER TABLE review_image
ADD CONSTRAINT fk_review_image_review
FOREIGN KEY (review_id) REFERENCES review(review_id);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;