# phonenumber_php

SQL
phpMyAdmin SQL Dump
version 5.2.1
https://www.phpmyadmin.net/
Server version: 10.4.32-MariaDB
PHP Version: 8.2.12

#Database: `phonenumber`
CREATE TABLE `phonenumber` (
  `id` int(11) NOT NULL,
  `phonenumber` varchar(20) NOT NULL,
  `UserID` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `category` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `tag` varchar(50) DEFAULT NULL
)

CREATE TABLE `total_category` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `total_tag` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users_collection` (
  `id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `isShowManagement` tinyint(1) DEFAULT 0,
  `isShowData` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) 

ALTER TABLE `phonenumber`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `total_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category` (`category`);

ALTER TABLE `total_tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`);

ALTER TABLE `users_collection`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Email` (`Email`);

ALTER TABLE `phonenumber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `total_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `total_tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `users_collection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;
