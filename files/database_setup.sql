--
-- Table structure for table `game_connections`
--

CREATE TABLE IF NOT EXISTS `game_connections` (
`id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `unique_code` int(4) NOT NULL,
  `host_ip_address` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `game_connections`
--
ALTER TABLE `game_connections`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `game_connections`
--
ALTER TABLE `game_connections`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- UPDATE TO game_connection table 7/4/2016
ALTER TABLE game_connections ADD COLUMN date DATE, ADD COLUMN game_active BOOL