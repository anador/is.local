create table categories (
cat_id int not NULL,
cat_name varchar(200) not null,
CONSTRAINT cat_id_pk PRIMARY KEY (cat_id)
);


create table goods (
good_id int not NULL,
good_name varchar(200) not null,
cat_id int not null,
CONSTRAINT good_id_pk PRIMARY KEY (good_id),
CONSTRAINT cat_id_fk FOREIGN KEY (cat_id) REFERENCES categories(cat_id)
);

create table pages_types (
type_id int not NULL,
type_name varchar(200),
CONSTRAINT type_id_pk PRIMARY KEY (type_id)
);

create table visits (
id int not NULL,
data_source varchar(200),
visit_time DATETIME,
uniq varchar(200),
ip varchar(200),
url VARCHAR(255),
type_id INT,
CONSTRAINT id_pk PRIMARY KEY (id),
CONSTRAINT type_id_fk FOREIGN KEY (type_id) REFERENCES pages_types(type_id)
);

create table pages_cats (
id int not NULL,
cat_id INT,
CONSTRAINT pages_cats_id_fk FOREIGN KEY (id) REFERENCES visits(id)
);


create table pages_carts (
id int not NULL,
cart_id INT,
good_id INT,
amount INT,
CONSTRAINT cart_id_pk PRIMARY KEY (cart_id),
CONSTRAINT pages_goods_good_id_fk FOREIGN KEY (good_id) REFERENCES goods(good_id)
);

alter table pages_cats
add constraint pages_cats_cat_id_fk FOREIGN KEY (cat_id) REFERENCES categories(cat_id)

alter table pages_goods
add constraint pages_good_good_id_fk FOREIGN KEY (good_id) REFERENCES goods(good_id)

create table pages_payments (
id int not NULL,
user_id BIGINT,
cart_id INT,
is_payed bool,
CONSTRAINT pages_payments_id_fk FOREIGN KEY (id) REFERENCES visits(id),
CONSTRAINT pages_payments_cart_id_fk FOREIGN KEY (cart_id) REFERENCES pages_carts(cart_id)
);

alter table pages_carts
add constraint pages_carts_id_fk FOREIGN KEY (id) REFERENCES visits(id)

LOAD DATA LOCAL INFILE 'D:\\cc2\\OSPanel\\domains\\is.local\\logs\\pages_types.txt' REPLACE INTO TABLE `shop`.`pages_types` CHARACTER SET utf8 FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' (`type_id`, `type_name`);
