-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 29 2024 г., 17:30
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `restPHP`
--

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE `files` (
  `id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`id`, `name`, `url`) VALUES
('7330d0dc38', 'urapobeda.jpg', 'http://localhost/files/7330d0dc38'),
('733b8c6136', 'iron-man-the-joker-funny-rainbows-tony-stark-robert-downey-jr-2560x1920.jpg', 'http://localhost/files/733b8c6136'),
('733b8cc2aa', 'd83a2091-b3bf-471f-bc02-e34d06cc1fb9.jpg', 'http://localhost/files/733b8cc2aa'),
('733b8d1500', 'ysWbALE3WEQ.jpg', 'http://localhost/files/733b8d1500'),
('733b8dcd6a', '1Монтажная-область-3 (1).png', 'http://localhost/files/733b8dcd6a'),
('733b8e2907', 'a8c496e7-83f8-463a-ab9d-baa53bb3ad4a.jpg', 'http://localhost/files/733b8e2907'),
('7389a06826', 'vsme.png', 'http://localhost/files/7389a06826');

-- --------------------------------------------------------

--
-- Структура таблицы `files_users`
--

CREATE TABLE `files_users` (
  `id` int NOT NULL,
  `file_id` varchar(10) NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `files_users`
--

INSERT INTO `files_users` (`id`, `file_id`, `user_id`, `type`) VALUES
(1, '7330d0dc38', 14, 'author'),
(2, '733b8c6136', 14, 'author'),
(3, '733b8cc2aa', 14, 'author'),
(4, '733b8d1500', 14, 'author'),
(5, '733b8dcd6a', 14, 'author'),
(6, '733b8e2907', 14, 'author'),
(11, '7330d0dc38', 12, 'co-author'),
(13, '7389a06826', 12, 'author'),
(14, '7389a06826', 14, 'co-author');

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE `sessions` (
  `id` int NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `date_open` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `token`, `date_open`) VALUES
(22, '12', 'c58ec451b4e35624692f43ff71358859', '2024-05-29 14:17:13');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `first_name`, `last_name`, `password`, `created`) VALUES
(2, 'sam.mraz1996@yahoo.com', '', '', '11', '2013-03-02 22:20:10'),
(3, 'liliane_hirt@gmail.com', '', '', '222', '2014-09-20 00:10:25'),
(4, 'michael2004@yahoo.com', '', '', '444', '2015-04-11 01:11:12'),
(5, 'krystel_wol7@gmail.com', '', '', '333', '2016-01-04 02:20:30'),
(6, 'neva_gutman10@hotmail.com', '', '', '555', '2017-01-10 03:40:10'),
(7, 'davonte.maye@yahoo.com', '', '', '666', '2017-05-01 23:20:30'),
(8, 'joesph.quitz@yahoo.com', '', '', '777', '2018-01-04 02:15:35'),
(9, 'jeramie_roh@hotmail.com', '', '', '888', '2019-01-01 23:20:30'),
(10, 'summer_shanah@hotmail.com', '', '', '999', '2020-02-01 03:22:50'),
(12, 'johndoe@gmail.com', 'Oleg', 'Olegov', 'johnMEME11122', '2024-05-20 16:52:29'),
(13, 'johndoe@2222.com', 'Oleg', 'Olegov', 'johnMEME11122', '2024-05-20 16:52:37'),
(14, 'rodionvarzymov@gmail.com', 'Rodion', 'Goshev', 'Rodionvarzymov17$%', '2024-05-29 13:24:25');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `files_users`
--
ALTER TABLE `files_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `files_users`
--
ALTER TABLE `files_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
