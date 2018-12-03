-- phpMyAdmin SQL Dump
-- version 4.0.10.20
-- https://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Pon 03. pro 2018, 21:48
-- Verze serveru: 5.6.40
-- Verze PHP: 5.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `xsopfp00`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `borrowing`
--

CREATE TABLE IF NOT EXISTS `borrowing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `literature_id` int(11) NOT NULL,
  `borrowing_date` date NOT NULL,
  `return_until_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `borrowing_user` (`user_id`),
  KEY `borrowing_literature` (`literature_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=10 ;

--
-- Vypisuji data pro tabulku `borrowing`
--

INSERT INTO `borrowing` (`id`, `user_id`, `literature_id`, `borrowing_date`, `return_until_date`, `return_date`) VALUES
(3, 1, 16, '2018-12-03', '2018-12-19', '2018-12-03'),
(5, 2, 22, '2018-12-03', '2018-12-26', '2018-12-03'),
(6, 2, 23, '2018-12-03', '2019-01-03', '2018-12-03'),
(8, 2, 21, '2018-12-03', '2019-01-03', NULL),
(9, 2, 15, '2018-12-03', '2019-01-03', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `literature`
--

CREATE TABLE IF NOT EXISTS `literature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `publisher` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `subtitle` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `pages_number` int(11) NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `pieces_total` int(11) NOT NULL DEFAULT '0',
  `pieces_borrowed` int(11) NOT NULL DEFAULT '0',
  `image` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=24 ;

--
-- Vypisuji data pro tabulku `literature`
--

INSERT INTO `literature` (`id`, `isbn`, `title`, `publisher`, `subtitle`, `publication_date`, `pages_number`, `description`, `pieces_total`, `pieces_borrowed`, `image`) VALUES
(9, '9781400201655', 'Girl, Wash Your Face', 'Nelson Books', 'Stop Believing the Lies about Who You Are So You Can Become Who You Were Meant to Be', '0000-00-00', 240, 'With wry wit and hard-earned wisdom, popular online personality and founder of TheChicSite.com Rachel Hollis helps readers break free from the lies keeping them from the joy-filled and exuberant life they are meant to have.', 20, 0, 'http://books.google.com/books/content?id=UFKltAEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(12, '9780545261241', 'The Wonky Donkey', 'Scholastic Inc.', NULL, '0000-00-00', 24, 'While walking down the road, the narrator sees a donkey that he shares more about as the story progresses.', 5, 0, 'http://books.google.com/books/content?id=t6lYb7WDHHoC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api'),
(13, '0545010225', 'Harry Potter and the Deathly Hallows', 'Pottermore', '', '2015-01-10', 759, 'In Harry Potter and the Deathly Hallows, the seventh and final book in the epic tale of Harry Potter, Harry and Lord Voldemort each prepare for their ultimate encounter. Voldemort takes control of the Ministry of Magic, installs Severus Snape as headmaster at Hogwarts, and sends his Death Eaters across the country to wreak havoc and find Harry. Meanwhile, Harry, Ron, and Hermione embark on a desperate quest the length and breadth of Britain, trying to locate and destroy Voldemort’s four remaining Horcruxes, the magical objects in which he has hidden parts of his broken soul. They visit the Burrow, Grimmauld Place, the Ministry, Godric’s Hollow, Malfoy Manor, Diagon Alley…But every time they solve one mystery, three more evolve—and not just about Voldemort, but about Dumbledore, and Harry’s own past, and three mysterious objects called the Deathly Hallows. The Hallows are literally things out of a children’s tale, which, if real, promise to make their possessor the “Master of Death;” and they ensnare Harry with their tantalizing claim of invulnerability. It is only after a nigh-unbearable loss that he is brought back to his true purpose, and the trio returns to Hogwarts for the final breathtaking battle between the forces of good and evil. They fight the Death Eaters alongside members of the Order of the Phoenix, Dumbledore’s Army, the Weasley clan, and the full array of Hogwarts teachers and students. Yet everything turns upon the moment the entire series has been building up to, the same meeting with which our story began: the moment when Harry and Voldemort face each other at last.', 30, 0, 'http://books.google.com/books/content?id=H8sdBgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(14, '9780316273947', 'Target: Alex Cross', 'Little, Brown', NULL, '2018-11-19', 432, 'TARGET: HEAD OF STATE A leader has fallen, and the procession route from Capitol Hill to the White House is lined with hundreds of thousands of mourners. None feel the loss of a President more keenly than Alex Cross, who has devoted his life to the public good. TARGET: UNITED STATES CABINET A sniper''s bullet strikes a target in the heart of DC. Alex Cross''s wife, Bree Stone, newly elevated chief of DC detectives, faces an ultimatum: solve the case, or lose the position for which she''s worked her entire career. The Secret Service and the FBI deploy as well in the race to find the shooter. Alex is tasked by the new President to take a personal role with the FBI, leading an investigation unprecedented in scale and scope. TARGET: ALEX CROSS Alex has a horrible premonition: is the sniper''s strike only the beginning of a larger attack on the nation? It isn''t long before his fears explode into life, and the nation plunges into a full-blown Constitutional crisis. His ingenuity, his training, and his capacity for battle are tested beyond limits in the most far-reaching and urgently consequential case of his life. As the rule of law is shattered by chaos, and Alex fights to isolate a suspect, Alex''s loyalty may be the biggest danger of all.', 40, 0, 'http://books.google.com/books/content?id=5ZDnswEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(15, '9780316225908', 'Two Kinds of Truth', 'Little, Brown', '', '0000-00-00', 400, 'Harry Bosch searches for the truth in the new thriller from #1 NYT bestselling author Michael Connelly Harry Bosch is back as a volunteer working cold cases for the San Fernando Police Department and is called out to a local drug store where a young pharmacist has been murdered. Bosch and the town''s 3-person detective squad sift through the clues, which lead into the dangerous, big business world of pill mills and prescription drug abuse. Meanwhile, an old case from Bosch''s LAPD days comes back to haunt him when a long-imprisoned killer claims Harry framed him, and seems to have new evidence to prove it. Bosch left the LAPD on bad terms, so his former colleagues aren''t keen to protect his reputation. He must fend for himself in clearing his name and keeping a clever killer in prison. The two unrelated cases wind around each other like strands of barbed wire. Along the way Bosch discovers that there are two kinds of truth: the kind that sets you free and the kind that leaves you buried in darkness.', 1, 1, 'http://books.google.com/books/content?id=kZR_nQAACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(16, '9781501190599', 'Red War', 'Atria/Emily Bestler Books', NULL, '2018-09-25', 384, '*Instant #1 New York Times Bestseller* The #1 New York Times bestselling series returns with Mitch Rapp racing to prevent Russia’s gravely ill leader from starting a full-scale war with NATO. “Mills is the only writer capable of filling the enormous void left by Vince Flynn.”—The Real Book Spy When Russian president Maxim Krupin discovers that he has inoperable brain cancer, he’s determined to cling to power. His first task is to kill or imprison any of his countrymen who can threaten him. Soon, though, his illness becomes serious enough to require a more dramatic diversion—war with the West. Upon learning of Krupin’s condition, CIA director Irene Kennedy understands that the US is facing an opponent who has nothing to lose. The only way to avoid a confrontation that could leave millions dead is to send Mitch Rapp to Russia under impossibly dangerous orders. With the Kremlin’s entire security apparatus hunting him, he must find and kill a man many have deemed the most powerful in the world. Success means averting a war that could consume all of Europe. But if his mission is discovered, Rapp will plunge Russia and America into a conflict that neither will survive. “In the world of black-ops thrillers, Mitch Rapp continues to be among the best of the best” (Booklist, starred review).', 25, 0, 'http://books.google.com/books/content?id=2jlvDwAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api'),
(17, '9781524796280', 'Fire and Blood', 'Bantam', '300 Years Before A Game of Thrones (A Targaryen History)', '2018-11-20', 640, 'An upcoming book to be published by Penguin Random House.', 22, 0, 'http://books.google.com/books/content?id=YjhrtgEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(18, '9781538761571', 'Long Road to Mercy', 'Grand Central Publishing', NULL, '2018-11-13', 432, '#1 New York Times bestselling author David Baldacci introduces a remarkable new character: Atlee Pine, an FBI special agent assigned to the remote wilds of the western United States. Ever since her twin sister was abducted by a notorious serial killer at age five, Atlee has spent her life hunting down those who hurt others. And she''s the best at it. She could be one of the Bureau''s top criminal profilers, if she didn''t prefer catching criminals in the vast wilderness of the West to climbing the career ladder in the D.C. office. Her chosen mission is a lonesome one--but that suits her just fine. Now, Atlee is called in to investigate the mutilated carcass of a mule found in the Grand Canyon--and hopefully, solve the disappearance of its rider. But this isn''t the only recent disappearance. In fact, it may be just the first clue, the key to unraveling a rash of other similar missing persons cases in the canyon....', 17, 0, 'http://books.google.com/books/content?id=hCRIswEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(21, '9780439023481', 'The Hunger Games', 'Unknown', 'The First Book of the Hunger Games', '0000-00-00', 374, 'In a future North America, where the rulers of Panem maintain control through an annual televised survival competition pitting young people from each of the twelve districts against one another, sixteen-year-old Katniss''s skills are put to the test when she voluntarily takes her younger sister''s place.', 64, 1, 'http://books.google.com/books/content?id=sJdUAzLUNyAC&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
(22, '9781444778519', 'Angelfall', 'Hodder Paperbacks', '', '0000-00-00', 325, 'The official print edition of the internet phenomenon. Already over 8,000 five star different reader reviews. (And counting.) It''s been six weeks since the angels of the apocalypse destroyed the world as we know it. Only pockets of humanity remain. Savage street gangs rule the day while fear and superstition rule the night. When angels fly away with a helpless girl, her seventeen-year-old sister Penryn will do anything to get her back...', 51, 0, ''),
(23, '9780064410939', 'Charlotte''s Web (full color)', 'Harper Collins', NULL, '2001-10-02', 192, 'Beloved by generations, Charlotte''s Web and Stuart Little are two of the most cherished stories of all time. Now, for the first time ever, these treasured classics are available in lavish new collectors'' editions. In addition to a larger trim size, the original black-and-white art by Garth Williams has been lovingly colorized by renowned illustrator Rosemary Wells, adding another dimension to these two perfect books for young and old alike.', 74, 0, 'http://books.google.com/books/content?id=Knx-xeDs72sC&printsec=frontcover&img=1&zoom=1&source=gbs_api');

-- --------------------------------------------------------

--
-- Struktura tabulky `literature_has_author`
--

CREATE TABLE IF NOT EXISTS `literature_has_author` (
  `literature_id` int(11) NOT NULL,
  `author` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`literature_id`,`author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `literature_has_author`
--

INSERT INTO `literature_has_author` (`literature_id`, `author`) VALUES
(9, 'Rachel Hollis'),
(12, 'Craig Smith'),
(12, 'Katz Cowley'),
(13, 'Rowling, J.K.'),
(14, 'James Patterson'),
(15, 'Michael Connelly'),
(16, 'Kyle Mills'),
(16, 'Vince Flynn'),
(17, 'George R. R. Martin'),
(18, 'David Baldacci'),
(21, 'Suzanne Collins'),
(22, 'Susan Ee'),
(23, 'E. B. White');

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `last_name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `member_until` date NOT NULL,
  `employee` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=16 ;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`id`, `name`, `last_name`, `email`, `password`, `telephone`, `birthdate`, `member_until`, `employee`) VALUES
(1, 'Jon', 'Snow', 'ctenar@ctenar.cz', '$2y$10$saDKkBaywbhFpbtdCqU7Ie.zsef6TZylhqnOpFtxX./a/46Ke0Ele', '', '1998-12-18', '2019-01-24', 0),
(2, 'Peter', 'Parker', 'zamestnanec@zamestnanec.cz', '$2y$10$pMwGHQRjT5w9w1To7lc3Xep.vlL5ECcvxziK6n/N1dnlXPlBzLBMe', '774126169', '1991-06-05', '2019-03-21', 1),
(15, 'Petr', 'Šopf', 'trsak1@seznam.cz', '$2y$10$EWbGWh1KADuAKfYjL/haSe2lpz9NRfU7vS8O.Mw9ObSfcyycc4n8C', '774126169', '2018-12-10', '2020-01-24', 0);

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `borrowing`
--
ALTER TABLE `borrowing`
  ADD CONSTRAINT `borrowing_literature` FOREIGN KEY (`literature_id`) REFERENCES `literature` (`id`),
  ADD CONSTRAINT `borrowing_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `literature_has_author`
--
ALTER TABLE `literature_has_author`
  ADD CONSTRAINT `literature_has_author_literature` FOREIGN KEY (`literature_id`) REFERENCES `literature` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
