CI USED this ALL tables
this will help you make migration

--
-- Database: `rullart_rullart`
--

---

--
-- Table structure for table `addressbook`
--

CREATE TABLE `addressbook` (
`addressid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`firstname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`lastname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`mobile` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`country` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`fkareaid` int(11) DEFAULT NULL,
`address` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`securityid` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`city` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`block_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`house_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`avenue_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`street_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`building_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`floor_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`flat_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`fkcountryid` int(11) DEFAULT NULL,
`is_default` tinyint(4) NOT NULL DEFAULT 0,
`phaseid` int(11) DEFAULT NULL,
`phase` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseAR` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`additionalinstruction` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`delivery_method` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
`id` bigint(20) UNSIGNED NOT NULL,
`user` varchar(20) DEFAULT NULL,
`pass` varchar(32) DEFAULT NULL,
`name` tinytext DEFAULT NULL,
`email` tinytext DEFAULT NULL,
`site` int(10) UNSIGNED DEFAULT 0,
`user_role` int(11) DEFAULT NULL,
`d_add` date DEFAULT NULL,
`d_mod` bigint(20) UNSIGNED DEFAULT NULL,
`lock_access` int(11) DEFAULT NULL,
`fkstoreid` int(11) DEFAULT NULL,
`created_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `apilog`
--

CREATE TABLE `apilog` (
`apiid` int(11) NOT NULL,
`url` varchar(500) NOT NULL,
`msg` varchar(5000) NOT NULL,
`requeston` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `areamaster`
--

CREATE TABLE `areamaster` (
`areaid` int(11) NOT NULL,
`fkcountryid` int(11) NOT NULL,
`areaname` varchar(100) DEFAULT NULL,
`areanameAR` varchar(100) DEFAULT NULL,
`isactive` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `calendar`
--

CREATE TABLE `calendar` (
`datefield` date DEFAULT NULL,
`ordercnt` int(11) DEFAULT 0,
`salesamt` decimal(18,2) DEFAULT 0.00
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
`cartitemid` int(11) NOT NULL,
`fkcartid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`qty` int(11) NOT NULL,
`price` decimal(18,3) NOT NULL,
`actualprice` decimal(18,3) NOT NULL,
`size` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`sizename` varchar(50) DEFAULT NULL,
`discount` decimal(18,2) NOT NULL,
`subtotal` decimal(18,3) NOT NULL,
`photo` varchar(100) NOT NULL,
`fkstatusid` int(11) DEFAULT NULL,
`giftproductid` int(11) DEFAULT 0,
`giftproductid2` int(11) DEFAULT 0,
`giftproductid3` int(11) NOT NULL DEFAULT 0,
`giftproductid4` int(11) DEFAULT NULL,
`internation_ship` int(11) NOT NULL DEFAULT 0,
`giftproductprice` decimal(18,3) DEFAULT 0.000,
`giftproduct2price` decimal(18,3) DEFAULT 0.000,
`giftproduct3price` decimal(18,3) DEFAULT 0.000,
`giftproduct4price` float DEFAULT NULL,
`giftboxprice` decimal(18,3) DEFAULT 0.000,
`giftmessageid` int(11) NOT NULL DEFAULT 0,
`giftmessage` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`giftqty` int(11) DEFAULT 0,
`gifttitle` varchar(500) NOT NULL,
`gifttitleAR` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`giftmessage_charge` decimal(18,3) NOT NULL DEFAULT 0.000,
`gift_type` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `cartmaster`
--

CREATE TABLE `cartmaster` (
`cartid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`fkstoreid` int(11) DEFAULT 1,
`orderdate` timestamp NOT NULL DEFAULT current_timestamp(),
`itemtotal` decimal(18,3) NOT NULL,
`shipping_charge` decimal(18,3) DEFAULT NULL,
`total` decimal(18,3) NOT NULL,
`fkorderstatus` int(11) NOT NULL,
`paymentmethod` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`addressid` int(11) NOT NULL,
`firstname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`lastname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`title` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`country` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`areaname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`address` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`firstnameBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`lastnameBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`mobileBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`countryBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`areanameBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`addressBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`payid` int(11) DEFAULT NULL,
`paymentid` int(11) DEFAULT NULL,
`trackid` int(11) DEFAULT NULL,
`lang` varchar(5) DEFAULT NULL,
`asGift` tinyint(1) DEFAULT 0,
`giftMessage` varchar(1500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`successIndicator` varchar(50) DEFAULT NULL,
`city` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`block_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`house_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`avenue_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`street_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`cityBill` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`block_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`house_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`avenue_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`street_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`securityid` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`building_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`floor_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`flat_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`building_numberBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`floor_numberBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`flat_numberBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`currencycode` varchar(5) NOT NULL,
`currencyrate` decimal(10,6) NOT NULL,
`remindersend` int(11) NOT NULL DEFAULT 0,
`couponcode` varchar(100) DEFAULT NULL,
`couponvalue` decimal(18,3) DEFAULT NULL,
`discount` decimal(18,3) DEFAULT NULL,
`shipping_method` varchar(100) DEFAULT NULL,
`phaseid` int(11) DEFAULT NULL,
`additionalinstruction` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`delivery_method` varchar(100) DEFAULT NULL,
`vat_percent` decimal(18,2) NOT NULL DEFAULT 0.00,
`vat` decimal(18,3) NOT NULL DEFAULT 0.000,
`phase` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseAR` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseidBill` int(11) DEFAULT NULL,
`additionalinstructionBill` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`delivery_methodBill` varchar(100) DEFAULT NULL,
`phaseBill` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseARBill` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`ismobile` int(11) DEFAULT NULL,
`appversion` varchar(500) DEFAULT NULL,
`mobiledevice` varchar(100) DEFAULT NULL,
`token` varchar(100) DEFAULT NULL,
`browser` varchar(100) DEFAULT NULL,
`platform` varchar(100) DEFAULT NULL,
`emailcount` int(11) NOT NULL DEFAULT 0,
`emailsenddate` datetime DEFAULT NULL,
`created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
`categoryid` int(11) NOT NULL,
`category` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`categoryAR` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`categorycode` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`ispublished` tinyint(1) NOT NULL,
`showmenu` tinyint(1) NOT NULL,
`displayorder` smallint(6) NOT NULL,
`parentid` int(11) NOT NULL DEFAULT 0,
`metakeyword` varchar(1000) DEFAULT NULL,
`metadescr` varchar(1500) DEFAULT NULL,
`metatitle` varchar(500) DEFAULT NULL,
`updateddate` datetime NOT NULL,
`updatedby` int(11) NOT NULL,
`photo` varchar(255) DEFAULT NULL,
`photo_mobile` varchar(100) DEFAULT NULL,
`metatitleAR` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`metadescrAR` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`metakeywordAR` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`category_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`category_description_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`category_other_details_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`category_other_details_title_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`category_other_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`category_other_details_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `commonmaster`
--

CREATE TABLE `commonmaster` (
`commonid` int(11) NOT NULL,
`commonname` varchar(100) NOT NULL,
`commonvalue` varchar(100) NOT NULL,
`commonvalueAR` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`displayorder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `countrymaster`
--

CREATE TABLE `countrymaster` (
`countryid` int(11) NOT NULL,
`countryname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`countrynameAR` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`isactive` tinyint(1) NOT NULL DEFAULT 1,
`shipping_charge` decimal(18,2) NOT NULL,
`shipping_days` varchar(1000) DEFAULT '',
`shipping_daysAR` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
`currencycode` varchar(5) NOT NULL,
`currencyrate` decimal(10,6) NOT NULL,
`currencysymbol` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`free_shipping_over` decimal(18,3) DEFAULT 0.000,
`isocode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `couponcode`
--

CREATE TABLE `couponcode` (
`couponcodeid` int(11) NOT NULL,
`couponcode` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`couponvalue` decimal(10,0) NOT NULL,
`isactive` tinyint(1) NOT NULL DEFAULT 1,
`isgeneral` tinyint(1) DEFAULT 0,
`fkcoupontypeid` int(11) DEFAULT 1,
`startdate` date DEFAULT NULL,
`enddate` date DEFAULT NULL,
`ismultiuse` tinyint(1) DEFAULT NULL,
`coupontype` varchar(20) DEFAULT NULL,
`fkcategoryid` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `coupontype`
--

CREATE TABLE `coupontype` (
`coupontypeid` int(11) NOT NULL,
`coupontype` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `courier_company`
--

CREATE TABLE `courier_company` (
`id` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`tracking_url` text DEFAULT NULL,
`isactive` tinyint(1) NOT NULL DEFAULT 1,
`created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

---

--
-- Table structure for table `customercoupon`
--

CREATE TABLE `customercoupon` (
`customercouponid` int(11) NOT NULL,
`fkcustomerid` int(11) DEFAULT NULL,
`couponcode` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`email` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`expirydate` date DEFAULT NULL,
`createdon` datetime DEFAULT NULL,
`createdby` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
`customerid` int(11) NOT NULL,
`firstname` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`lastname` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`email` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`password` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`last_login` datetime NOT NULL,
`login_ipaddress` varchar(50) NOT NULL,
`login_type` varchar(20) NOT NULL,
`isactive` tinyint(1) NOT NULL,
`language` char(2) NOT NULL,
`createdon` timestamp NOT NULL DEFAULT current_timestamp(),
`updatedby` int(11) NOT NULL,
`updateddate` datetime NOT NULL,
`oauth_provider` varchar(255) DEFAULT NULL,
`oauth_uid` varchar(255) DEFAULT NULL,
`gender` varchar(10) DEFAULT NULL,
`locale` varchar(10) DEFAULT NULL,
`picture_url` varchar(255) DEFAULT NULL,
`profile_url` varchar(255) DEFAULT NULL,
`token` varchar(18) DEFAULT NULL,
`wishlist_email` timestamp NULL DEFAULT NULL,
`wishlist_update` timestamp NULL DEFAULT NULL,
`isnewsletter` tinyint(1) DEFAULT 0,
`fkstoreid` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `customers_devices`
--

CREATE TABLE `customers_devices` (
`deviceid` int(11) NOT NULL,
`device_uid` varchar(500) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`device_name` varchar(100) NOT NULL,
`device_version` varchar(100) DEFAULT NULL,
`device_otherdetails` varchar(2000) DEFAULT NULL,
`update_date` datetime NOT NULL,
`badge` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
`id` int(11) NOT NULL,
`rate` decimal(18,2) DEFAULT NULL,
`startdate` date DEFAULT NULL,
`days` int(11) DEFAULT NULL,
`enddate` date DEFAULT NULL,
`isactive` bit(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `emailtemplate`
--

CREATE TABLE `emailtemplate` (
`emailtemplateid` int(11) NOT NULL,
`subject` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`emailtype` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`body` longtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`createdon` datetime DEFAULT NULL,
`completedon` datetime DEFAULT NULL,
`status` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `filtermaster`
--

CREATE TABLE `filtermaster` (
`filterid` int(11) NOT NULL,
`filtername` varchar(200) NOT NULL,
`filternameAR` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`filtercode` varchar(200) NOT NULL,
`isactive` tinyint(1) NOT NULL,
`updatedby` int(11) NOT NULL,
`updateddate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `filtervalues`
--

CREATE TABLE `filtervalues` (
`filtervalueid` int(11) NOT NULL,
`fkfilterid` int(11) NOT NULL,
`filtervalue` varchar(200) NOT NULL,
`filtervalueAR` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`filtervaluecode` varchar(200) NOT NULL,
`isactive` tinyint(1) NOT NULL DEFAULT 1,
`displayorder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `homegallery`
--

CREATE TABLE `homegallery` (
`homegalleryid` int(11) NOT NULL,
`title` varchar(250) DEFAULT NULL,
`descr` varchar(5000) DEFAULT NULL,
`titleAR` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`descrAR` varchar(1500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`link` varchar(500) NOT NULL,
`photo` varchar(250) NOT NULL,
`photo_mobile` varchar(200) DEFAULT NULL,
`photo_ar` varchar(250) DEFAULT NULL,
`photo_mobile_ar` varchar(250) DEFAULT NULL,
`updateddate` datetime NOT NULL,
`updatedby` int(11) NOT NULL,
`displayorder` int(11) NOT NULL,
`ispublished` tinyint(1) NOT NULL,
`videourl` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
`id` int(11) NOT NULL,
`uri` varchar(255) NOT NULL,
`method` varchar(6) NOT NULL,
`params` text DEFAULT NULL,
`api_key` varchar(40) NOT NULL,
`ip_address` varchar(45) NOT NULL,
`time` int(11) NOT NULL,
`rtime` float DEFAULT NULL,
`authorized` varchar(1) NOT NULL,
`response_code` smallint(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `mantrajap_device`
--

CREATE TABLE `mantrajap_device` (
`id` int(11) NOT NULL,
`fkleadform_id` int(11) NOT NULL,
`device_id` varchar(255) NOT NULL,
`os` varchar(64) DEFAULT NULL,
`version` varchar(64) DEFAULT NULL,
`device_name` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `menu_master`
--

CREATE TABLE `menu_master` (
`id` int(11) NOT NULL,
`parent_id` int(11) NOT NULL,
`menu_name` varchar(100) NOT NULL,
`page_url` varchar(255) NOT NULL,
`icon_name` varchar(50) NOT NULL,
`display_order` int(11) NOT NULL,
`is_active` tinyint(1) NOT NULL DEFAULT 0,
`is_delete` tinyint(1) NOT NULL DEFAULT 0,
`is_direct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
`messageid` int(11) NOT NULL,
`message` varchar(500) DEFAULT NULL,
`messageAR` varchar(500) DEFAULT NULL,
`isactive` tinyint(1) DEFAULT 0,
`displayorder` int(11) NOT NULL DEFAULT 99,
`displayorderAR` int(11) NOT NULL DEFAULT 99
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `metatags`
--

CREATE TABLE `metatags` (
`metatagsid` int(11) NOT NULL,
`metatitle` varchar(400) NOT NULL,
`metadescr` varchar(800) NOT NULL,
`metakeyword` varchar(500) NOT NULL,
`url` varchar(500) NOT NULL,
`updatedby` int(11) NOT NULL,
`updateddate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
`newsletterid` int(11) NOT NULL,
`subject` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`body` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`createdon` datetime DEFAULT NULL,
`status` smallint(6) DEFAULT NULL COMMENT '0=Pending, 1 = Process, 2=Stop , 3 = Completed',
`completedon` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `newsletteremails`
--

CREATE TABLE `newsletteremails` (
`emailid` int(11) NOT NULL,
`fknewsletterid` int(11) DEFAULT NULL,
`email` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`status` smallint(6) DEFAULT 0,
`sendon` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
`notificationid` int(11) NOT NULL,
`fkcustomerid` int(11) DEFAULT NULL,
`device_uid` varchar(500) DEFAULT NULL,
`title` varchar(500) DEFAULT NULL,
`message` varchar(5000) DEFAULT NULL,
`isread` tinyint(1) DEFAULT 0,
`createdby` int(11) DEFAULT NULL,
`createdon` datetime DEFAULT NULL,
`response` varchar(1000) DEFAULT NULL,
`badge` int(11) DEFAULT NULL,
`redirect_type` varchar(100) DEFAULT NULL,
`redirect_code` varchar(500) DEFAULT NULL,
`photo` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `occassion`
--

CREATE TABLE `occassion` (
`occassionid` int(11) NOT NULL,
`occassion` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`occassionAR` varchar(150) NOT NULL,
`occassioncode` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`photo` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`ispublished` tinyint(1) NOT NULL,
`metakeyword` varchar(1000) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`metadescr` varchar(1500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`metatitle` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`updateddate` datetime NOT NULL,
`updatedby` int(11) NOT NULL,
`showhome` tinyint(1) NOT NULL,
`photo_mobile` varchar(100) DEFAULT NULL,
`metatitleAR` varchar(500) DEFAULT NULL,
`metadescrAR` varchar(1000) DEFAULT NULL,
`metakeywordAR` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
`orderitemid` int(11) NOT NULL,
`fkorderid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`qty` int(11) NOT NULL,
`price` decimal(18,3) NOT NULL,
`actualprice` decimal(18,3) NOT NULL,
`size` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`sizename` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`discount` decimal(18,2) NOT NULL,
`subtotal` decimal(18,3) NOT NULL,
`photo` varchar(100) NOT NULL,
`fkstatusid` int(11) DEFAULT NULL,
`giftproductid` int(11) DEFAULT 0,
`giftproductid2` int(11) DEFAULT 0,
`giftproductid3` int(11) NOT NULL DEFAULT 0,
`giftproductid4` int(11) DEFAULT NULL,
`internation_ship` int(11) NOT NULL DEFAULT 0,
`giftproductprice` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftproduct2price` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftproduct3price` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftproduct4price` varchar(255) DEFAULT NULL,
`giftboxprice` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftmessageid` int(11) NOT NULL DEFAULT 0,
`giftmessage` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`gifttitle` varchar(500) NOT NULL,
`gifttitleAR` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`giftqty` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `orderlimit_emails`
--

CREATE TABLE `orderlimit_emails` (
`orderlimitid` int(11) NOT NULL,
`fkcustomerid` int(11) DEFAULT NULL,
`email` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`ordertotal` decimal(18,3) DEFAULT NULL,
`totallimit` decimal(18,3) DEFAULT NULL,
`sendon` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `orderlog`
--

CREATE TABLE `orderlog` (
`orderlogid` int(11) NOT NULL,
`fkorderid` int(11) NOT NULL,
`fkuserid` int(11) NOT NULL,
`fkorderstatus` int(11) NOT NULL,
`descr` varchar(500) NOT NULL,
`actionon` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `ordermaster`
--

CREATE TABLE `ordermaster` (
`orderid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`fkstoreid` int(11) DEFAULT 1,
`orderdate` timestamp NOT NULL DEFAULT current_timestamp(),
`itemtotal` decimal(18,3) NOT NULL,
`shipping_charge` decimal(18,3) DEFAULT NULL,
`total` decimal(18,3) NOT NULL,
`fkorderstatus` int(11) NOT NULL,
`paymentmethod` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`addressid` int(11) NOT NULL,
`firstname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`lastname` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`title` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`country` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`areaname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`address` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`firstnameBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`lastnameBill` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`mobileBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`countryBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`areanameBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`addressBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`payid` int(11) DEFAULT NULL,
`paymentid` varchar(100) DEFAULT NULL,
`tranid` varchar(100) DEFAULT NULL,
`lang` varchar(5) DEFAULT NULL,
`asGift` tinyint(1) DEFAULT 0,
`giftMessage` varchar(1500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`successIndicator` varchar(50) DEFAULT NULL,
`city` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`block_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`house_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`avenue_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`street_number` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`cityBill` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`block_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`house_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`avenue_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`street_numberBill` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`securityid` varchar(155) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`building_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`floor_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`flat_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`building_numberBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`floor_numberBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`flat_numberBill` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`trackingno` varchar(50) DEFAULT NULL,
`courier_company` varchar(255) DEFAULT NULL,
`trackingphoto` varchar(500) DEFAULT NULL,
`currencycode` varchar(5) NOT NULL,
`currencyrate` decimal(10,6) NOT NULL,
`fkcartid` int(11) NOT NULL DEFAULT 0,
`mobiledevice` varchar(100) DEFAULT NULL,
`browser` varchar(100) DEFAULT NULL,
`platform` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`ismobile` tinyint(1) NOT NULL DEFAULT 0,
`couponcode` varchar(100) DEFAULT NULL,
`couponvalue` decimal(18,3) DEFAULT NULL,
`discount` decimal(18,3) DEFAULT NULL,
`isread` tinyint(1) NOT NULL DEFAULT 0,
`shipping_method` varchar(100) DEFAULT NULL,
`phaseid` int(11) DEFAULT NULL,
`additionalinstruction` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`delivery_method` varchar(100) DEFAULT NULL,
`vat_percent` decimal(18,2) NOT NULL DEFAULT 0.00,
`vat` decimal(18,3) NOT NULL DEFAULT 0.000,
`phase` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseAR` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseidBill` int(11) DEFAULT NULL,
`additionalinstructionBill` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`delivery_methodBill` varchar(100) DEFAULT NULL,
`phaseBill` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`phaseARBill` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`appversion` varchar(500) DEFAULT NULL,
`approvalcode` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `orderstatus`
--

CREATE TABLE `orderstatus` (
`statusid` int(11) NOT NULL,
`status` varchar(100) NOT NULL,
`statusAR` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`classname` varchar(50) NOT NULL,
`classname2` varchar(50) NOT NULL,
`displayorder` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
`pageid` int(11) NOT NULL,
`pagetitle` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`pagetitleAR` varchar(200) NOT NULL,
`pagename` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`photo` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`details` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`detailsAR` longtext NOT NULL,
`metatitle` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`metakeyword` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`metadescription` varchar(1500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`updateddate` datetime NOT NULL,
`fkuserid` int(11) NOT NULL,
`published` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `pages_mobile`
--

CREATE TABLE `pages_mobile` (
`pageid` int(11) NOT NULL,
`pagetitle` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`pagetitleAR` varchar(200) NOT NULL,
`pagename` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`photo` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`details` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
`detailsAR` longtext NOT NULL,
`metatitle` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`metakeyword` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`metadescription` varchar(1500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`updateddate` datetime NOT NULL,
`fkuserid` int(11) NOT NULL,
`published` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `pay`
--

CREATE TABLE `pay` (
`payid` int(11) NOT NULL,
`firstname` varchar(100) DEFAULT NULL,
`lastname` varchar(100) DEFAULT NULL,
`email` varchar(100) DEFAULT NULL,
`orderid` int(11) DEFAULT NULL,
`amount` decimal(18,3) DEFAULT NULL,
`paydate` datetime DEFAULT NULL,
`paystatus` int(11) DEFAULT NULL,
`transid` varchar(100) DEFAULT NULL,
`paymentid` int(11) DEFAULT NULL,
`submiton` timestamp NULL DEFAULT current_timestamp(),
`successIndicator` varchar(50) DEFAULT NULL,
`resultIndicator` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
`payid` int(11) NOT NULL,
`paymentid` bigint(20) NOT NULL,
`result` varchar(800) NOT NULL,
`postdate` varchar(500) DEFAULT NULL,
`tranid` varchar(100) DEFAULT NULL,
`auth` varchar(100) DEFAULT NULL,
`ref` varchar(100) DEFAULT NULL,
`trackid` varchar(100) DEFAULT NULL,
`udf1` varchar(500) DEFAULT NULL,
`udf2` varchar(500) DEFAULT NULL,
`udf3` varchar(500) DEFAULT NULL,
`udf4` varchar(500) DEFAULT NULL,
`udf5` varchar(500) DEFAULT NULL,
`submiton` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
`fkcustomerid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `payments_applepay`
--

CREATE TABLE `payments_applepay` (
`paymentid` int(255) NOT NULL,
`fkcartid` varchar(255) NOT NULL,
`amount` varchar(255) NOT NULL,
`fkcustomerid` varchar(255) NOT NULL,
`resultIndicator` text NOT NULL,
`sessionVersion` varchar(255) NOT NULL,
`submiton` datetime NOT NULL,
`response` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `payments_creditcard`
--

CREATE TABLE `payments_creditcard` (
`paymentid` int(11) NOT NULL,
`fkcartid` int(11) NOT NULL,
`amount` decimal(18,3) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`resultIndicator` varchar(50) NOT NULL,
`sessionid` varchar(50) NOT NULL,
`version` varchar(50) NOT NULL,
`successIndicator` varchar(50) DEFAULT NULL,
`sessionVersion` varchar(50) DEFAULT NULL,
`submiton` datetime NOT NULL,
`status` varchar(100) DEFAULT NULL,
`timeOfRecord` datetime DEFAULT NULL,
`authorizationCode` varchar(100) DEFAULT NULL,
`receipt` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `payments_qpay`
--

CREATE TABLE `payments_qpay` (
`qpayid` int(11) NOT NULL,
`status` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`referenceId` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`transactionId` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`amount` decimal(18,3) DEFAULT NULL,
`reason` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`cardtype` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`trandatetime` datetime DEFAULT NULL,
`submiton` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `payments_tabby`
--

CREATE TABLE `payments_tabby` (
`tabbyid` int(11) NOT NULL,
`status` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`reference` int(11) DEFAULT NULL,
`paymentid` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`created_at` datetime DEFAULT NULL,
`is_test` smallint(6) DEFAULT NULL,
`amount` decimal(18,2) DEFAULT NULL,
`currency` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `payment_myfatoorah`
--

CREATE TABLE `payment_myfatoorah` (
`payid` int(11) NOT NULL,
`InvoiceId` varchar(20) DEFAULT NULL,
`InvoiceStatus` varchar(20) DEFAULT NULL,
`InvoiceReference` varchar(20) DEFAULT NULL,
`CustomerReference` varchar(20) DEFAULT NULL,
`CreatedDate` datetime DEFAULT NULL,
`ExpiryDate` varchar(20) DEFAULT NULL,
`InvoiceValue` decimal(18,3) DEFAULT NULL,
`Comments` varchar(1500) DEFAULT NULL,
`CustomerName` varchar(100) DEFAULT NULL,
`CustomerMobile` varchar(50) DEFAULT NULL,
`CustomerEmail` varchar(100) DEFAULT NULL,
`UserDefinedField` varchar(30) DEFAULT NULL,
`InvoiceDisplayValue` varchar(50) DEFAULT NULL,
`TransactionDate` datetime DEFAULT NULL,
`PaymentGateway` varchar(50) DEFAULT NULL,
`ReferenceId` varchar(30) DEFAULT NULL,
`TrackId` varchar(30) DEFAULT NULL,
`TransactionId` varchar(30) DEFAULT NULL,
`PaymentId` varchar(30) DEFAULT NULL,
`PaymentId_query` varchar(30) DEFAULT NULL,
`AuthorizationId` varchar(30) DEFAULT NULL,
`TransactionStatus` varchar(20) DEFAULT NULL,
`TransationValue` decimal(18,3) DEFAULT NULL,
`CustomerServiceCharge` decimal(18,3) DEFAULT NULL,
`DueValue` decimal(18,3) DEFAULT NULL,
`PaidCurrency` varchar(10) DEFAULT NULL,
`PaidCurrencyValue` decimal(18,3) DEFAULT NULL,
`Currency` varchar(10) DEFAULT NULL,
`Error` varchar(500) DEFAULT NULL,
`CardNumber` varchar(50) DEFAULT NULL,
`response` varchar(5000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `productrating`
--

CREATE TABLE `productrating` (
`rateid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`rate` int(11) NOT NULL,
`review` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`fkorderid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`submiton` timestamp NOT NULL DEFAULT current_timestamp(),
`ispublished` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
`productid` int(11) NOT NULL,
`fkcategoryid` int(11) NOT NULL,
`title` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`titleAR` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`productcode` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`shortdescr` varchar(1800) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`shortdescrAR` varchar(1800) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`longdescr` longtext NOT NULL,
`longdescrAR` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`price` decimal(18,3) NOT NULL,
`discount` decimal(18,2) NOT NULL,
`start_discount` date DEFAULT NULL,
`end_discount` date DEFAULT NULL,
`temp_discount` decimal(18,2) DEFAULT NULL,
`temp_sellingprice` decimal(18,3) DEFAULT NULL,
`sellingprice` decimal(18,3) NOT NULL,
`metakeyword` varchar(1000) DEFAULT NULL,
`metadescr` varchar(1500) DEFAULT NULL,
`metatitle` varchar(500) NOT NULL,
`ispublished` tinyint(1) NOT NULL,
`isnew` tinyint(1) NOT NULL DEFAULT 0,
`ispopular` tinyint(1) NOT NULL,
`updateddate` datetime DEFAULT NULL,
`updatedby` int(11) NOT NULL DEFAULT 1,
`photo1` varchar(100) NOT NULL,
`photo2` varchar(100) NOT NULL,
`photo3` varchar(100) NOT NULL,
`photo4` varchar(100) NOT NULL,
`photo5` varchar(100) NOT NULL,
`productcode_old` varchar(150) DEFAULT NULL,
`internation_ship` tinyint(1) NOT NULL DEFAULT 0,
`productcategoryid` int(11) DEFAULT NULL,
`productcategoryid2` int(11) DEFAULT NULL,
`productcategoryid3` int(11) DEFAULT 0,
`productcategoryid4` int(11) DEFAULT NULL,
`isgift` tinyint(1) NOT NULL,
`gift_type` int(11) DEFAULT 0,
`video` varchar(500) DEFAULT NULL,
`videoposter` varchar(500) DEFAULT NULL,
`metatitleAR` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`metadescrAR` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`metakeywordAR` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
`shuffle_sorting` int(11) DEFAULT 0,
`related_category_2` int(11) DEFAULT NULL,
`related_category_1` int(11) DEFAULT NULL,
`related_products` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `productsfilter`
--

CREATE TABLE `productsfilter` (
`fkproductid` int(11) NOT NULL,
`fkfiltervalueid` int(11) NOT NULL,
`filtercode` varchar(50) NOT NULL,
`qty` int(11) NOT NULL,
`fkstoreid` int(11) NOT NULL DEFAULT 1,
`barcode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `productsfilter_deleted`
--

CREATE TABLE `productsfilter_deleted` (
`fkproductid` int(11) NOT NULL,
`fkfiltervalueid` int(11) NOT NULL,
`filtercode` varchar(50) NOT NULL,
`qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `productsphoto`
--

CREATE TABLE `productsphoto` (
`productimageid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`photoname` varchar(200) NOT NULL,
`displayorder` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `productsstock`
--

CREATE TABLE `productsstock` (
`stockid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`fkproductfilterid` int(11) DEFAULT NULL,
`qty` int(11) NOT NULL,
`prevqty` int(11) NOT NULL,
`action` varchar(500) NOT NULL,
`updateddate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
`updatedby` int(11) NOT NULL,
`fkorderid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `products_deleted`
--

CREATE TABLE `products_deleted` (
`productid` int(11) NOT NULL,
`fkcategoryid` int(11) NOT NULL,
`title` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`titleAR` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`productcode` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`shortdescr` varchar(1800) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`shortdescrAR` varchar(1800) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`longdescr` longtext NOT NULL,
`longdescrAR` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
`price` decimal(18,3) NOT NULL,
`discount` decimal(18,2) NOT NULL,
`sellingprice` decimal(18,3) NOT NULL,
`metakeyword` varchar(1000) DEFAULT NULL,
`metadescr` varchar(1500) DEFAULT NULL,
`metatitle` varchar(500) NOT NULL,
`ispublished` tinyint(1) NOT NULL,
`isnew` tinyint(1) NOT NULL DEFAULT 0,
`ispopular` tinyint(1) NOT NULL,
`updateddate` datetime DEFAULT NULL,
`updatedby` int(11) NOT NULL DEFAULT 1,
`photo1` varchar(100) NOT NULL,
`photo2` varchar(100) NOT NULL,
`photo3` varchar(100) NOT NULL,
`photo4` varchar(100) NOT NULL,
`photo5` varchar(100) NOT NULL,
`productcode_old` varchar(150) DEFAULT NULL,
`internation_ship` tinyint(1) NOT NULL DEFAULT 0,
`productcategoryid` int(11) DEFAULT NULL,
`productcategoryid2` int(11) DEFAULT NULL,
`isgift` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `returnrequest`
--

CREATE TABLE `returnrequest` (
`requestid` int(11) NOT NULL,
`firstname` varchar(50) DEFAULT NULL,
`lastname` varchar(50) DEFAULT NULL,
`orderno` varchar(20) DEFAULT NULL,
`email` varchar(80) DEFAULT NULL,
`mobile` varchar(50) DEFAULT NULL,
`reason` varchar(5000) DEFAULT NULL,
`submiton` datetime DEFAULT NULL,
`lang` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
`settingid` int(11) NOT NULL,
`name` varchar(100) NOT NULL,
`details` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`inputtype` varchar(15) NOT NULL,
`isrequired` bit(1) NOT NULL,
`displayorder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `shoppingcart`
--

CREATE TABLE `shoppingcart` (
`shoppingcartid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`sessionid` varchar(50) NOT NULL,
`fkaddressbookid` int(11) NOT NULL,
`totalqty` int(11) NOT NULL,
`totalamt` decimal(18,2) NOT NULL,
`fkstatusid` smallint(6) NOT NULL,
`updatedon` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `shoppingcartitem`
--

CREATE TABLE `shoppingcartitem` (
`shoppingcartitemid` int(11) NOT NULL,
`fkshoppingcartid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`qty` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `shoppingcartitems`
--

CREATE TABLE `shoppingcartitems` (
`cartitemid` int(11) NOT NULL,
`fkcartid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`title` varchar(100) NOT NULL,
`qty` int(11) NOT NULL,
`price` decimal(18,3) NOT NULL,
`sellingprice` decimal(18,3) NOT NULL,
`size` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`sizename` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`discount` decimal(18,2) NOT NULL,
`subtotal` decimal(18,3) NOT NULL,
`photo` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`fkstatusid` int(11) DEFAULT NULL,
`giftproductid` int(11) DEFAULT 0,
`giftproductid2` int(11) DEFAULT 0,
`giftproductid3` int(11) NOT NULL DEFAULT 0,
`giftproductid4` int(11) DEFAULT NULL,
`createdon` datetime DEFAULT NULL,
`internation_ship` int(11) NOT NULL DEFAULT 0,
`giftproductprice` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftproduct2price` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftproduct3price` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftproduct4price` varchar(255) DEFAULT NULL,
`giftboxprice` decimal(18,3) NOT NULL DEFAULT 0.000,
`giftmessageid` int(11) NOT NULL DEFAULT 0,
`giftqty` int(11) DEFAULT 0,
`giftmessage` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`gifttitle` varchar(500) NOT NULL,
`gifttitleAR` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`giftmessage_charge` decimal(18,3) NOT NULL DEFAULT 0.000,
`gift_type` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `shoppingcartmaster`
--

CREATE TABLE `shoppingcartmaster` (
`cartid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`fkstoreid` int(11) DEFAULT 1,
`sessionid` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`orderdate` timestamp NOT NULL DEFAULT current_timestamp(),
`itemtotal` decimal(18,3) NOT NULL,
`shipping_charge` decimal(18,3) NOT NULL,
`giftbox_charge` decimal(18,3) NOT NULL DEFAULT 0.000,
`total` decimal(18,3) NOT NULL,
`paymentmethod` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`addressid` int(11) NOT NULL,
`lang` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`asGift` tinyint(1) DEFAULT 0,
`giftMessage` varchar(1500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`billingaddressid` int(11) NOT NULL DEFAULT 0,
`shippingaddressid` int(11) NOT NULL DEFAULT 0,
`couponcode` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`couponvalue` decimal(18,2) NOT NULL,
`discount` decimal(18,2) NOT NULL,
`shippingcountryid` int(11) NOT NULL,
`shipping_method` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
`phaseid` int(11) DEFAULT NULL,
`additionalinstruction` varchar(2000) DEFAULT NULL,
`phase` varchar(100) DEFAULT NULL,
`phaseAR` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`phaseidBill` int(11) DEFAULT NULL,
`additionalinstructionBill` varchar(2000) DEFAULT NULL,
`delivery_methodBill` varchar(100) DEFAULT NULL,
`phaseBill` varchar(100) DEFAULT NULL,
`phaseARBill` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
`delivery_method` varchar(100) DEFAULT NULL,
`vat_percent` decimal(18,2) NOT NULL DEFAULT 0.00,
`vat` decimal(18,3) NOT NULL DEFAULT 0.000,
`free_shipping_over` decimal(18,3) DEFAULT NULL,
`free_shipping_text` varchar(200) DEFAULT NULL,
`ismobile` int(11) NOT NULL DEFAULT 0,
`mobiledevice` varchar(100) NOT NULL,
`browser` varchar(100) NOT NULL,
`platform` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `social_media`
--

CREATE TABLE `social_media` (
`isactive` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

---

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
`storeid` int(11) NOT NULL DEFAULT 0,
`storename` varchar(600) DEFAULT NULL,
`storenameAR` varchar(600) DEFAULT NULL,
`fkcountryid` int(11) DEFAULT NULL,
`isactive` tinyint(1) DEFAULT NULL,
`updatedby` int(11) DEFAULT NULL,
`updateddate` datetime DEFAULT NULL,
`website` varchar(300) DEFAULT NULL,
`country` varchar(300) DEFAULT NULL,
`countryAR` varchar(300) DEFAULT NULL,
`currencycode` varchar(30) DEFAULT NULL,
`displayorder` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `store_session`
--

CREATE TABLE `store_session` (
`id` int(255) NOT NULL,
`session_id` varchar(255) DEFAULT NULL,
`origin` varchar(255) DEFAULT NULL,
`ip_address` varchar(255) DEFAULT NULL,
`device` varchar(255) DEFAULT NULL,
`created_at` datetime NOT NULL,
`added_to_cart` tinyint(1) DEFAULT 0,
`reached_checkout` tinyint(1) DEFAULT 0,
`converted` tinyint(1) DEFAULT 0,
`type` varchar(255) DEFAULT NULL,
`session_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

---

--
-- Table structure for table `unsubscribe`
--

CREATE TABLE `unsubscribe` (
`email` varchar(255) NOT NULL,
`submiton` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
`userid` bigint(20) UNSIGNED NOT NULL,
`user` varchar(20) DEFAULT NULL,
`password` varchar(100) DEFAULT NULL,
`name` tinytext DEFAULT NULL,
`email` tinytext DEFAULT NULL,
`site` int(10) UNSIGNED DEFAULT 0,
`user_role` int(11) DEFAULT NULL,
`d_add` date DEFAULT NULL,
`d_mod` bigint(20) UNSIGNED DEFAULT NULL,
`isactive` int(11) DEFAULT NULL,
`fkstoreid` int(11) DEFAULT NULL,
`created_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `user_audit_trails`
--

CREATE TABLE `user_audit_trails` (
`id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
`event` enum('insert','update','delete') NOT NULL,
`table_name` varchar(128) NOT NULL,
`old_values` text DEFAULT NULL,
`new_values` text NOT NULL,
`url` varchar(255) NOT NULL,
`name` varchar(128) NOT NULL,
`ip_address` varchar(45) NOT NULL,
`user_agent` varchar(255) NOT NULL,
`created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

---

--
-- Table structure for table `user_rights`
--

CREATE TABLE `user_rights` (
`id` int(11) NOT NULL,
`role_id` int(11) NOT NULL,
`menu_id` int(11) NOT NULL,
`view_button` tinyint(1) NOT NULL DEFAULT 1,
`add_button` tinyint(1) NOT NULL DEFAULT 0,
`edit_button` tinyint(1) NOT NULL DEFAULT 0,
`delete_button` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
`role_id` int(11) NOT NULL,
`role_name` varchar(100) NOT NULL,
`is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
`wishlistid` int(11) NOT NULL,
`fkproductid` int(11) NOT NULL,
`fkcustomerid` int(11) NOT NULL,
`createdon` timestamp NULL DEFAULT current_timestamp(),
`emailsenddate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addressbook`
--
ALTER TABLE `addressbook`
ADD PRIMARY KEY (`addressid`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `apilog`
--
ALTER TABLE `apilog`
ADD PRIMARY KEY (`apiid`);

--
-- Indexes for table `areamaster`
--
ALTER TABLE `areamaster`
ADD PRIMARY KEY (`areaid`);

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
ADD PRIMARY KEY (`cartitemid`);

--
-- Indexes for table `cartmaster`
--
ALTER TABLE `cartmaster`
ADD PRIMARY KEY (`cartid`),
ADD KEY `fkcustomerid` (`fkcustomerid`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
ADD PRIMARY KEY (`categoryid`),
ADD UNIQUE KEY `uniq-category` (`category`),
ADD UNIQUE KEY `categorycode` (`categorycode`),
ADD UNIQUE KEY `categoryAR` (`categoryAR`);

--
-- Indexes for table `commonmaster`
--
ALTER TABLE `commonmaster`
ADD PRIMARY KEY (`commonid`),
ADD UNIQUE KEY `commonname` (`commonname`,`commonvalue`);

--
-- Indexes for table `countrymaster`
--
ALTER TABLE `countrymaster`
ADD PRIMARY KEY (`countryid`);

--
-- Indexes for table `couponcode`
--
ALTER TABLE `couponcode`
ADD PRIMARY KEY (`couponcodeid`),
ADD UNIQUE KEY `couponcode` (`couponcode`);

--
-- Indexes for table `coupontype`
--
ALTER TABLE `coupontype`
ADD PRIMARY KEY (`coupontypeid`);

--
-- Indexes for table `courier_company`
--
ALTER TABLE `courier_company`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customercoupon`
--
ALTER TABLE `customercoupon`
ADD PRIMARY KEY (`customercouponid`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
ADD PRIMARY KEY (`customerid`),
ADD UNIQUE KEY `customer_email` (`email`);

--
-- Indexes for table `customers_devices`
--
ALTER TABLE `customers_devices`
ADD PRIMARY KEY (`deviceid`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emailtemplate`
--
ALTER TABLE `emailtemplate`
ADD PRIMARY KEY (`emailtemplateid`);

--
-- Indexes for table `filtermaster`
--
ALTER TABLE `filtermaster`
ADD PRIMARY KEY (`filterid`);

--
-- Indexes for table `filtervalues`
--
ALTER TABLE `filtervalues`
ADD PRIMARY KEY (`filtervalueid`),
ADD UNIQUE KEY `fkfilterid` (`fkfilterid`,`filtervalue`);

--
-- Indexes for table `homegallery`
--
ALTER TABLE `homegallery`
ADD PRIMARY KEY (`homegalleryid`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mantrajap_device`
--
ALTER TABLE `mantrajap_device`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_master`
--
ALTER TABLE `menu_master`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
ADD PRIMARY KEY (`messageid`);

--
-- Indexes for table `metatags`
--
ALTER TABLE `metatags`
ADD PRIMARY KEY (`metatagsid`),
ADD UNIQUE KEY `url` (`url`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
ADD PRIMARY KEY (`newsletterid`);

--
-- Indexes for table `newsletteremails`
--
ALTER TABLE `newsletteremails`
ADD PRIMARY KEY (`emailid`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
ADD PRIMARY KEY (`notificationid`);

--
-- Indexes for table `occassion`
--
ALTER TABLE `occassion`
ADD PRIMARY KEY (`occassionid`),
ADD UNIQUE KEY `uniq-occassion` (`occassion`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
ADD PRIMARY KEY (`orderitemid`);

--
-- Indexes for table `orderlimit_emails`
--
ALTER TABLE `orderlimit_emails`
ADD PRIMARY KEY (`orderlimitid`);

--
-- Indexes for table `orderlog`
--
ALTER TABLE `orderlog`
ADD PRIMARY KEY (`orderlogid`);

--
-- Indexes for table `ordermaster`
--
ALTER TABLE `ordermaster`
ADD PRIMARY KEY (`orderid`),
ADD KEY `fkcustomerid` (`fkcustomerid`);

--
-- Indexes for table `orderstatus`
--
ALTER TABLE `orderstatus`
ADD PRIMARY KEY (`statusid`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
ADD PRIMARY KEY (`pageid`),
ADD UNIQUE KEY `titleunique` (`pagename`);

--
-- Indexes for table `pages_mobile`
--
ALTER TABLE `pages_mobile`
ADD PRIMARY KEY (`pageid`),
ADD UNIQUE KEY `titleunique` (`pagename`);

--
-- Indexes for table `pay`
--
ALTER TABLE `pay`
ADD PRIMARY KEY (`payid`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
ADD PRIMARY KEY (`payid`);

--
-- Indexes for table `payments_applepay`
--
ALTER TABLE `payments_applepay`
ADD PRIMARY KEY (`paymentid`);

--
-- Indexes for table `payments_creditcard`
--
ALTER TABLE `payments_creditcard`
ADD PRIMARY KEY (`paymentid`);

--
-- Indexes for table `payments_qpay`
--
ALTER TABLE `payments_qpay`
ADD PRIMARY KEY (`qpayid`);

--
-- Indexes for table `payments_tabby`
--
ALTER TABLE `payments_tabby`
ADD PRIMARY KEY (`tabbyid`);

--
-- Indexes for table `payment_myfatoorah`
--
ALTER TABLE `payment_myfatoorah`
ADD PRIMARY KEY (`payid`);

--
-- Indexes for table `productrating`
--
ALTER TABLE `productrating`
ADD PRIMARY KEY (`rateid`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
ADD PRIMARY KEY (`productid`),
ADD UNIQUE KEY `uniqueProductCode` (`productcode`),
ADD UNIQUE KEY `title` (`title`),
ADD UNIQUE KEY `titleAR` (`titleAR`);

--
-- Indexes for table `productsfilter`
--
ALTER TABLE `productsfilter`
ADD PRIMARY KEY (`fkproductid`,`fkfiltervalueid`,`filtercode`,`fkstoreid`),
ADD KEY `fkfiltervalueid` (`fkfiltervalueid`);

--
-- Indexes for table `productsfilter_deleted`
--
ALTER TABLE `productsfilter_deleted`
ADD PRIMARY KEY (`fkproductid`,`fkfiltervalueid`,`filtercode`),
ADD KEY `fkfiltervalueid` (`fkfiltervalueid`);

--
-- Indexes for table `productsphoto`
--
ALTER TABLE `productsphoto`
ADD PRIMARY KEY (`productimageid`);

--
-- Indexes for table `productsstock`
--
ALTER TABLE `productsstock`
ADD PRIMARY KEY (`stockid`);

--
-- Indexes for table `products_deleted`
--
ALTER TABLE `products_deleted`
ADD PRIMARY KEY (`productid`),
ADD UNIQUE KEY `uniqueProductCode` (`productcode`),
ADD UNIQUE KEY `title` (`title`),
ADD UNIQUE KEY `titleAR` (`titleAR`);

--
-- Indexes for table `returnrequest`
--
ALTER TABLE `returnrequest`
ADD PRIMARY KEY (`requestid`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
ADD PRIMARY KEY (`settingid`);

--
-- Indexes for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
ADD PRIMARY KEY (`shoppingcartid`);

--
-- Indexes for table `shoppingcartitem`
--
ALTER TABLE `shoppingcartitem`
ADD PRIMARY KEY (`shoppingcartitemid`);

--
-- Indexes for table `shoppingcartitems`
--
ALTER TABLE `shoppingcartitems`
ADD PRIMARY KEY (`cartitemid`);

--
-- Indexes for table `shoppingcartmaster`
--
ALTER TABLE `shoppingcartmaster`
ADD PRIMARY KEY (`cartid`),
ADD KEY `fkcustomerid` (`fkcustomerid`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
ADD PRIMARY KEY (`storeid`);

--
-- Indexes for table `store_session`
--
ALTER TABLE `store_session`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unsubscribe`
--
ALTER TABLE `unsubscribe`
ADD PRIMARY KEY (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `user_audit_trails`
--
ALTER TABLE `user_audit_trails`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_rights`
--
ALTER TABLE `user_rights`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
ADD PRIMARY KEY (`wishlistid`),
ADD UNIQUE KEY `fkproductid` (`fkproductid`,`fkcustomerid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addressbook`
--
ALTER TABLE `addressbook`
MODIFY `addressid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `apilog`
--
ALTER TABLE `apilog`
MODIFY `apiid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areamaster`
--
ALTER TABLE `areamaster`
MODIFY `areaid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
MODIFY `cartitemid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cartmaster`
--
ALTER TABLE `cartmaster`
MODIFY `cartid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
MODIFY `categoryid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commonmaster`
--
ALTER TABLE `commonmaster`
MODIFY `commonid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countrymaster`
--
ALTER TABLE `countrymaster`
MODIFY `countryid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `couponcode`
--
ALTER TABLE `couponcode`
MODIFY `couponcodeid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupontype`
--
ALTER TABLE `coupontype`
MODIFY `coupontypeid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courier_company`
--
ALTER TABLE `courier_company`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customercoupon`
--
ALTER TABLE `customercoupon`
MODIFY `customercouponid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
MODIFY `customerid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers_devices`
--
ALTER TABLE `customers_devices`
MODIFY `deviceid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emailtemplate`
--
ALTER TABLE `emailtemplate`
MODIFY `emailtemplateid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filtermaster`
--
ALTER TABLE `filtermaster`
MODIFY `filterid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filtervalues`
--
ALTER TABLE `filtervalues`
MODIFY `filtervalueid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `homegallery`
--
ALTER TABLE `homegallery`
MODIFY `homegalleryid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mantrajap_device`
--
ALTER TABLE `mantrajap_device`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_master`
--
ALTER TABLE `menu_master`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
MODIFY `messageid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `metatags`
--
ALTER TABLE `metatags`
MODIFY `metatagsid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
MODIFY `newsletterid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletteremails`
--
ALTER TABLE `newsletteremails`
MODIFY `emailid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
MODIFY `notificationid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `occassion`
--
ALTER TABLE `occassion`
MODIFY `occassionid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
MODIFY `orderitemid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderlimit_emails`
--
ALTER TABLE `orderlimit_emails`
MODIFY `orderlimitid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderlog`
--
ALTER TABLE `orderlog`
MODIFY `orderlogid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ordermaster`
--
ALTER TABLE `ordermaster`
MODIFY `orderid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderstatus`
--
ALTER TABLE `orderstatus`
MODIFY `statusid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
MODIFY `pageid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages_mobile`
--
ALTER TABLE `pages_mobile`
MODIFY `pageid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pay`
--
ALTER TABLE `pay`
MODIFY `payid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
MODIFY `payid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_applepay`
--
ALTER TABLE `payments_applepay`
MODIFY `paymentid` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_creditcard`
--
ALTER TABLE `payments_creditcard`
MODIFY `paymentid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_qpay`
--
ALTER TABLE `payments_qpay`
MODIFY `qpayid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_tabby`
--
ALTER TABLE `payments_tabby`
MODIFY `tabbyid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_myfatoorah`
--
ALTER TABLE `payment_myfatoorah`
MODIFY `payid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productrating`
--
ALTER TABLE `productrating`
MODIFY `rateid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
MODIFY `productid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productsstock`
--
ALTER TABLE `productsstock`
MODIFY `stockid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_deleted`
--
ALTER TABLE `products_deleted`
MODIFY `productid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returnrequest`
--
ALTER TABLE `returnrequest`
MODIFY `requestid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
MODIFY `settingid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shoppingcartitems`
--
ALTER TABLE `shoppingcartitems`
MODIFY `cartitemid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shoppingcartmaster`
--
ALTER TABLE `shoppingcartmaster`
MODIFY `cartid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_session`
--
ALTER TABLE `store_session`
MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `userid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_audit_trails`
--
ALTER TABLE `user_audit_trails`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_rights`
--
ALTER TABLE `user_rights`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
MODIFY `wishlistid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/_!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT _/;
/_!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS _/;
/_!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION _/;
