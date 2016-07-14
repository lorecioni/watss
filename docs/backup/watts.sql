
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `watts`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `camera`
--

CREATE TABLE `camera` (
  `cameraid` int(11) NOT NULL,
  `calibration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `groups`
--

CREATE TABLE `groups` (
  `groupid` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `people`
--

CREATE TABLE `people` (
  `peopleid` int(11) NOT NULL,
  `frameid` int(11) NOT NULL,
  `cameraid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `bb_x` int(11) NOT NULL,
  `bb_y` int(11) NOT NULL,
  `bb_width` int(11) NOT NULL,
  `bb_height` int(11) NOT NULL,
  `bbV_x` int(11) NOT NULL,
  `bbV_y` int(11) NOT NULL,
  `bbV_width` int(11) NOT NULL,
  `bbV_height` int(11) NOT NULL,
  `gazeAngle_face` int(3) NOT NULL,
  `gazeAngle_face_z` int(3) NOT NULL,
  `gazeAngle_body` int(3) NOT NULL,
  `gazeAngle_body_z` int(3) NOT NULL,
  `color` varchar(7) NOT NULL,
  `poiid` int(11) NOT NULL,
  `groupid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `poi`
--

CREATE TABLE `poi` (
  `poiid` int(11) NOT NULL,
  `cameraid` int(11) NOT NULL,
  `location_x` int(11) NOT NULL,
  `location_y` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `name` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `real_people`
--

CREATE TABLE `real_people` (
  `peopleid` int(11) NOT NULL,
  `face` int(11) NOT NULL,
  `face_z` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE `user` (
  `userid` int(11) NOT NULL,
  `name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `video`
--

CREATE TABLE `video` (
  `frameid` int(11) NOT NULL,
  `cameraid` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `camera`
--
ALTER TABLE `camera`
  ADD PRIMARY KEY (`cameraid`);

--
-- Indici per le tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`groupid`,`userid`),
  ADD KEY `userid` (`userid`);

--
-- Indici per le tabelle `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`peopleid`,`frameid`,`cameraid`,`userid`),
  ADD KEY `poiid` (`poiid`),
  ADD KEY `peopleid` (`peopleid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `cameraid` (`cameraid`),
  ADD KEY `groupid` (`groupid`),
  ADD KEY `frameid` (`frameid`);

--
-- Indici per le tabelle `poi`
--
ALTER TABLE `poi`
  ADD PRIMARY KEY (`poiid`,`cameraid`),
  ADD KEY `cameraid` (`poiid`,`cameraid`),
  ADD KEY `poi_fk_cameraid_idx` (`cameraid`);

--
-- Indici per le tabelle `real_people`
--
ALTER TABLE `real_people`
  ADD PRIMARY KEY (`peopleid`);

--
-- Indici per le tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userid`);

--
-- Indici per le tabelle `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`frameid`,`cameraid`),
  ADD KEY `frameid` (`frameid`,`cameraid`),
  ADD KEY `frameid_2` (`frameid`),
  ADD KEY `cameraid` (`cameraid`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `camera`
--
ALTER TABLE `camera`
  MODIFY `cameraid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `groups`
--
ALTER TABLE `groups`
  MODIFY `groupid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `real_people`
--
ALTER TABLE `real_people`
  MODIFY `peopleid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `user`
--
ALTER TABLE `user`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT;
--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `group_fk_userid` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Limiti per la tabella `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_fk_cameraid` FOREIGN KEY (`cameraid`) REFERENCES `camera` (`cameraid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `people_fk_frameid` FOREIGN KEY (`frameid`) REFERENCES `video` (`frameid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_fk_groupid` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_fk_poi` FOREIGN KEY (`poiid`) REFERENCES `poi` (`poiid`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_fk_userid` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Limiti per la tabella `poi`
--
ALTER TABLE `poi`
  ADD CONSTRAINT `poi_fk_cameraid` FOREIGN KEY (`cameraid`) REFERENCES `camera` (`cameraid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limiti per la tabella `real_people`
--
ALTER TABLE `real_people`
  ADD CONSTRAINT `real_people_fk_peopleid` FOREIGN KEY (`peopleid`) REFERENCES `people` (`peopleid`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Limiti per la tabella `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `video_fk_cameraid` FOREIGN KEY (`cameraid`) REFERENCES `camera` (`cameraid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
