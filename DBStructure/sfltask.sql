/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1_3306
Source Server Version : 50505
Source Host           : 127.0.0.1:3306
Source Database       : sfltask

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-03-28 08:25:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for generic
-- ----------------------------
DROP TABLE IF EXISTS `generic`;
CREATE TABLE `generic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `genericKey` varchar(20) NOT NULL,
  `genericId` int(11) NOT NULL,
  `genericName` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `genericKey` (`genericKey`,`genericId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of generic
-- ----------------------------
INSERT INTO `generic` VALUES ('3', 'userrole', '0', 'Admin');
INSERT INTO `generic` VALUES ('4', 'userrole', '1', 'Manager');
INSERT INTO `generic` VALUES ('5', 'userrole', '2', 'Waiter');

-- ----------------------------
-- Table structure for order_products
-- ----------------------------
DROP TABLE IF EXISTS `order_products`;
CREATE TABLE `order_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(11) unsigned NOT NULL,
  `productId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderId` (`orderId`,`productId`),
  KEY `productId` (`productId`),
  CONSTRAINT `order_products_ibfk_1` FOREIGN KEY (`orderId`) REFERENCES `user_table` (`id`),
  CONSTRAINT `order_products_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of order_products
-- ----------------------------
INSERT INTO `order_products` VALUES ('16', '6', '973');

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `productId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `imageName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `productName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`productId`),
  UNIQUE KEY `productName` (`productName`),
  KEY `products_ibfk_2` (`userId`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=976 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of products
-- ----------------------------
INSERT INTO `products` VALUES ('971', '1', '123.12', '2018-03-28 13:18:45', null, null, 'ArmineProduct');
INSERT INTO `products` VALUES ('973', '1', '123.12', '2018-03-28 13:30:23', null, null, 'ArminePttroduct');
INSERT INTO `products` VALUES ('974', '1', '123.12', '2018-03-28 13:41:50', 'ssaaaaa', null, 'AssrminePttroduct');
INSERT INTO `products` VALUES ('975', '1', '123.12', '2018-03-28 13:42:31', 'ssaaaaa', 'aaaaaaa', 'AassrminePttroduct');

-- ----------------------------
-- Table structure for tablelist
-- ----------------------------
DROP TABLE IF EXISTS `tablelist`;
CREATE TABLE `tablelist` (
  `tableId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `tableName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userCount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tableId`),
  UNIQUE KEY `tableName` (`tableName`),
  KEY `products_ibfk_2` (`userId`),
  CONSTRAINT `tablelist_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=973 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of tablelist
-- ----------------------------
INSERT INTO `tablelist` VALUES ('972', '1', 'ArminePttroduct', '2018-03-28 13:40:40', 'aaaaa', '5');

-- ----------------------------
-- Table structure for token
-- ----------------------------
DROP TABLE IF EXISTS `token`;
CREATE TABLE `token` (
  `token` char(102) COLLATE utf8_unicode_ci NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `createDate` datetime NOT NULL,
  `expireDate` datetime NOT NULL,
  `type` enum('PASSWORD_RESET','REGISTER','LOGIN','FORGOT_PASSWORD') COLLATE utf8_unicode_ci NOT NULL,
  `used` enum('YES','NO') COLLATE utf8_unicode_ci DEFAULT 'NO',
  PRIMARY KEY (`token`,`userId`,`type`),
  KEY `userId` (`userId`),
  CONSTRAINT `token_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of token
-- ----------------------------
INSERT INTO `token` VALUES ('63d9545318f9ec66a74c934357dc16f121a81f832d0e45aea246f36e6dad30e97d1c3f75fd2ff9570125b6e36586ae16', '2', '2018-03-28 10:38:34', '2018-03-28 16:04:04', 'LOGIN', 'YES');
INSERT INTO `token` VALUES ('77759c6ae69fd8fc2cd4a8070ff181c0cb0f2ef3ec096387cf182679a89d83de455a2240435016e8182726f2f4e6a830', '1', '2018-03-28 12:52:29', '2018-04-11 12:52:29', 'LOGIN', 'YES');
INSERT INTO `token` VALUES ('ca4f112c9f0d173028a933a09c5675494e7b18ff404bb3fcd438e0ccb9fb72b52b1e5f26144017d6b81b1eeeae18d9d1', '2', '2018-03-28 16:04:58', '2018-04-11 16:04:58', 'LOGIN', 'YES');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `passSalt` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passHash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registerDate` bigint(20) DEFAULT NULL,
  `registerIp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `loginToken` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` int(11) unsigned DEFAULT '1',
  PRIMARY KEY (`userId`),
  UNIQUE KEY `email` (`email`) USING BTREE,
  UNIQUE KEY `username` (`userName`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=30004 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'Armine1', '6tUbvyT9eApyyIl3kwVuY/cBOIjdZKEDLapM8cG1HrU=', '974053703be1c67bfc8e8c69d00d17b518e044b5e584cfc65628bdbf2426d911', 'armine1@test.com', '1522225896', '127.0.0.1', '77759c6ae69fd8fc2cd4a8070ff181c0cb0f2ef3ec096387cf182679a89d83de455a2240435016e8182726f2f4e6a830', '1');
INSERT INTO `users` VALUES ('2', 'Armine', 'qqJI4tTt/l778YDAYhQzUmOLjrp/Qq1YXI8PinZ2M6Q=', '4b123808160d8cf6786f4633def65dc7535da39a096e9452df7e3087e5cb3c82', 'armine@test.com', '1522222439', '127.0.0.1', 'ca4f112c9f0d173028a933a09c5675494e7b18ff404bb3fcd438e0ccb9fb72b52b1e5f26144017d6b81b1eeeae18d9d1', '0');
INSERT INTO `users` VALUES ('30003', 'Armaaaine', 'PIKDAMu1tJSwE3dUBf5aJ+Bhs5kyEQL0JVvSNCFOlK8=', '2ec29bfc9153dcea02aae9fe2dedae8201abaaa3362d990131c484b45807f8b2', 'armiaane@test.com', '1522245021', '127.0.0.1', '', '2');

-- ----------------------------
-- Table structure for user_table
-- ----------------------------
DROP TABLE IF EXISTS `user_table`;
CREATE TABLE `user_table` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tableId` int(11) unsigned NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `ordered` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '1-ordered',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tableId` (`tableId`),
  KEY `user_table_ibfk_1` (`userId`),
  CONSTRAINT `user_table_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  CONSTRAINT `user_table_ibfk_2` FOREIGN KEY (`tableId`) REFERENCES `tablelist` (`tableId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of user_table
-- ----------------------------
INSERT INTO `user_table` VALUES ('6', '972', '1', '1');
