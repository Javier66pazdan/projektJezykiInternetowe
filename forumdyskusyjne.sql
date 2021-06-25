-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 25 Cze 2021, 12:11
-- Wersja serwera: 10.4.19-MariaDB
-- Wersja PHP: 8.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `forumdyskusyjne`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `comment` longtext COLLATE utf8_unicode_ci NOT NULL,
  `createdOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `comments`
--

INSERT INTO `comments` (`id`, `userID`, `comment`, `createdOn`) VALUES
(39, 7, 'Pierwszy komentarz', '2021-05-24 20:33:20'),
(40, 7, 'Drugi   ', '2021-05-24 20:33:25'),
(41, 7, 'Trzeckk', '2021-05-24 20:38:50'),
(42, 7, 'Komentarz ', '2021-06-03 15:59:05'),
(43, 9, 'Heniu dodaje komentarz', '2021-06-13 10:16:12'),
(47, 7, 'Komentarz', '2021-06-23 15:31:09'),
(48, 11, 'awadawdawd', '2021-06-24 23:57:30');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `commentID` int(11) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `createdOn` datetime NOT NULL,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `replies`
--

INSERT INTO `replies` (`id`, `commentID`, `comment`, `createdOn`, `userID`) VALUES
(9, 43, 'Komentarz taki i taki', '2021-06-23 14:49:43', 10);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `createdOn`) VALUES
(1, 'pabo66', 'adw@dwa.com', '$2y$10$oiR9mgzREs32iZyFR43ysuNBy0vAbkQ127jvVd4XY4YmwR7RvTZ6W', '2021-05-21 16:41:07'),
(6, 'Adam', 'adam@wp.pl', '$2y$10$7uPvaGc098.N1nFKJlG9S.htTKMR0ORPXupNkJj9yMvyjKw0yPvq2', '2021-05-22 09:32:02'),
(7, 'Janek', 'janek@wp.pl', '$2y$10$eD8qESihleJyRspCOKfz..OcyZo2ui7YjEkS/JW1XvFPhAgc4kmMG', '2021-06-25 12:06:02'),
(8, 'wiesiek', 'wiesiek@wp.pl', '$2y$10$RuZMGH6qHzj0eLeT/sFSseGM4TQ.05o1v.ZDPLlqUPbIwwgMdG5CK', '2021-05-24 22:47:46'),
(9, 'Heniu', 'heniu@wp.pl', '$2y$10$uGpvLDyAwnV.uheSG8BqsOUAIt2lu2t5KO0c2uL7ttAECOUvgFswe', '2021-06-13 10:15:51'),
(10, 'Jozef', 'jozef@wp.pl', '$2y$10$JI9EopWjqF/uJ8OrP8x3J.IReaPE8W42JOXrF7IPnQh6cXn0/hMQ2', '2021-06-23 13:40:26'),
(11, 'admin', 'admin@wp.pl', '$2y$10$0wig5J1TiEy96JD5u55IXO2CdOdlInjL6BBNz3qazKD6tm8P/lRlG', '2021-06-23 15:07:02'),
(12, 'ad', 'ad@wad.com', '$2y$10$vega.1Lif5KZDeC3aQH2QeTVCc4ChEB08dFm0zJnsfc/wghQleAki', '2021-06-25 11:30:30');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- Indeksy dla tabeli `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commentID` (`commentID`),
  ADD KEY `userID` (`userID`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT dla tabeli `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`commentID`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
