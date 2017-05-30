-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
--
-- 호스트: localhost
-- 처리한 시간: 17-05-17 19:07
-- 서버 버전: 5.0.26
-- PHP 버전: 5.1.6
--
-- 데이터베이스: `mtns_kofic`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_boxoffice`
--

CREATE TABLE `kofic_boxoffice` (
  `Date` varchar(8) NOT NULL,
  `Rank` int(11) NOT NULL,
  `MovieCd` varchar(8) NOT NULL,
  `MovieNm` varchar(300) NOT NULL,
  PRIMARY KEY  (`Date`,`Rank`),
  KEY `Idx_MovieCd_Date` (`MovieCd`,`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_fix_boxoffice`
--

CREATE TABLE `kofic_fix_boxoffice` (
  `Date` varchar(8) NOT NULL,
  `Rank` int(11) NOT NULL,
  `MovieCd` varchar(8) NOT NULL,
  `MovieNm` varchar(300) NOT NULL,
  PRIMARY KEY  (`Date`,`Rank`),
  KEY `Idx_MovieCd_Date` (`MovieCd`,`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_fix_theather`
--

CREATE TABLE `kofic_fix_theather` (
  `Code` char(6) NOT NULL default '',
  `Location` enum('서울','경기강원','대전충청','대구경북','부산경남','광주호남') default NULL COMMENT '지역구분',
  `TheatherName` varchar(200) NOT NULL,
  `Group` enum('1','2','3','4','5','6','') default NULL COMMENT '''1'':CGV, ''2'':롯데, ''3'':메가박스, ''4'':프리머스, '''':그외',
  `Active` tinyint(1) default '1',
  PRIMARY KEY  (`Code`),
  KEY `Idx_active` (`Active`),
  KEY `Idx_group` (`Group`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_movie`
--

CREATE TABLE `kofic_movie` (
  `Code` varchar(8) NOT NULL default '',
  `MovieName` varchar(300) default NULL,
  PRIMARY KEY  (`Code`),
  KEY `Idx_MovieName` (`MovieName`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_playing`
--

CREATE TABLE `kofic_playing` (
  `TheatherCd` char(6) NOT NULL,
  `Date` varchar(8) NOT NULL,
  `ScrnNm` varchar(200) NOT NULL,
  `Inning` int(11) NOT NULL,
  `ShowTm` varchar(4) NOT NULL,
  `MovieCd` varchar(8) NOT NULL,
  `MovieNm` varchar(300) NOT NULL,
  `UnitPrice` int(11) NOT NULL,
  PRIMARY KEY  (`TheatherCd`,`Date`,`ScrnNm`,`Inning`,`MovieCd`),
  KEY `Idx_ScmNm` (`Date`,`MovieCd`,`TheatherCd`,`ScrnNm`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_screen`
--

CREATE TABLE `kofic_screen` (
  `TheatherCd` char(6) NOT NULL,
  `ScrnCd` varchar(2) NOT NULL,
  `ScrnNm` varchar(200) NOT NULL,
  `Seat` decimal(5,0) NOT NULL,
  PRIMARY KEY  (`TheatherCd`,`ScrnCd`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_seat`
--

CREATE TABLE `kofic_seat` (
  `TheatherCd` char(6) NOT NULL,
  `ScrnNm` varchar(200) NOT NULL,
  `Seat` int(11) NOT NULL,
  PRIMARY KEY  (`TheatherCd`,`ScrnNm`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_showroom`
--

CREATE TABLE `kofic_showroom` (
  `TheatherCd` char(6) NOT NULL,
  `Date` varchar(8) NOT NULL,
  `ScrnNm` varchar(200) NOT NULL,
  `MovieCd` varchar(8) NOT NULL,
  `MovieNm` varchar(300) NOT NULL,
  `Seat` int(11) NOT NULL,
  PRIMARY KEY  (`TheatherCd`,`Date`,`ScrnNm`),
  KEY `Idx_MovieCd_Date_TheatherCD` (`MovieCd`,`Date`,`TheatherCd`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_showtime`
--

CREATE TABLE `kofic_showtime` (
  `TheatherCd` char(6) NOT NULL,
  `Date` varchar(8) NOT NULL,
  `ScrnNm` varchar(200) NOT NULL,
  `Seq` int(11) NOT NULL,
  `ShowTm` varchar(4) NOT NULL,
  `MovieCd` varchar(8) NOT NULL,
  `MovieNm` varchar(300) NOT NULL,
  PRIMARY KEY  (`TheatherCd`,`Date`,`ScrnNm`,`Seq`,`MovieCd`),
  KEY `idx_date_moviecd` (`Date`,`MovieCd`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `kofic_theather`
--

CREATE TABLE `kofic_theather` (
  `Code` char(6) NOT NULL default '',
  `TheatherName` varchar(200) default NULL,
  `Seat` decimal(5,0) NOT NULL,
  PRIMARY KEY  (`Code`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `lotto`
--

CREATE TABLE `lotto` (
  `Inning` int(11) NOT NULL,
  `No1` int(11) NOT NULL,
  `No2` int(11) NOT NULL,
  `No3` int(11) NOT NULL,
  `No4` int(11) NOT NULL,
  `No5` int(11) NOT NULL,
  `No6` int(11) NOT NULL,
  `No7` int(11) NOT NULL,
  PRIMARY KEY  (`Inning`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

-- --------------------------------------------------------

--
-- 테이블 구조 `wrk_job`
--

CREATE TABLE `wrk_job` (
  `JobCode` varchar(8) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `Percent` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=euckr;




CREATE TABLE `cgv_movies` (
  `moviecode` varchar(5) NOT NULL,
  `moviename` varchar(100) NOT NULL,
  `releasedate` varchar(8) NOT NULL,
  PRIMARY KEY  (`moviecode`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;

CREATE TABLE `cgv_theater` (
    `theatercode` varchar(4) NOT NULL,
    `regioncode` varchar(2) NOT NULL,
    `regionname` varchar(10) NOT NULL,
    `theatername` varchar(100) NOT NULL,
  PRIMARY KEY  (`theatercode`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;



CREATE TABLE `cgv_regions` (
    `regioncode` varchar(2) NOT NULL,
    `regionname` varchar(10) NOT NULL,
  PRIMARY KEY  (`regioncode`)
) ENGINE=MyISAM DEFAULT CHARSET=euckr;
