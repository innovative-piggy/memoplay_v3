/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1_3306
 Source Server Type    : MySQL
 Source Server Version : 100410
 Source Host           : 127.0.0.1:3306
 Source Schema         : memonpja_orders

 Target Server Type    : MySQL
 Target Server Version : 100410
 File Encoding         : 65001

 Date: 02/12/2020 10:01:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders`  (
  `order_id_n` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `order_id` int(11) NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `email` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `firstname` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `lastname` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `city` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `country` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `titre_musique` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
  `phrase_personnalisee` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cover_image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
  `spotify_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
  `taille` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
  `datep` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `quantity` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `SKU` varchar(22) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `shopify_id` bigint(20) NULL DEFAULT NULL,
  PRIMARY KEY (`order_id_n`) USING BTREE,
  UNIQUE INDEX `order_id_n`(`order_id_n`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for zip
-- ----------------------------
DROP TABLE IF EXISTS `zip`;
CREATE TABLE `zip`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datep` int(11) NOT NULL,
  `startp` int(11) NOT NULL,
  `endp` int(11) NOT NULL,
  `fails` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 36 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
