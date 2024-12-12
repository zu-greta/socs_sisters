-- load some data into the database
-- users
-- 1|gzu|g|z|gzu@gmail.com|$2y$10$QqRYYJ3bkG3f3C8U7PrlSutn2npB7Fh/MHjzUmdqWXHWB0n4mJcVK|2024-12-09 18:16:14|2024-12-09 18:16:14
-- 2|kwang|k|w|kwang@gmail.com|$2y$10$QJJlJAWBkO6vBbNIikegtOLrZvPvZg1lWvZ88ZuSGIs0P3mpIrxwe|2024-12-09 18:16:37|2024-12-09 18:16:37
INSERT INTO Users values (1, "g", "z", "gzu@gmail.com", "$2y$10$QqRYYJ3bkG3f3C8U7PrlSutn2npB7Fh/MHjzUmdqWXHWB0n4mJcVK","2024-12-09 18:16:14", "2024-12-09 18:16:14");
INSERT INTO Users values (2, "k", "w", "kwang@gmail.com", "$2y$10$QJJlJAWBkO6vBbNIikegtOLrZvPvZg1lWvZ88ZuSGIs0P3mpIrxwe","2024-12-09 18:16:37", "2024-12-09 18:16:37");
-- events
-- 594|1|2024-12-10|2024-12-10|15|22:08:00|22:23:00|jajajaj||2024-12-10 04:27:51|hghghg|11|https://example.com/scheduling/event?creator_id=1
-- 595|2|2024-12-10|2024-12-10|15|10:08:00|10:20:00|heheh||2024-12-09 10:27:51|jeje|5|url
-- user 1 made events 594, 1 and 2
-- user 2 made events 595, 49, and 101 
INSERT INTO Events values (594, 1, "2024-12-20", "2024-12-20", 15, "22:08:00", "22:23:00", "594", "", "2024-12-10 04:27:51", "hghghg", 1, "https://example.com/scheduling/event?creator_id=1");
INSERT INTO Events values (595, 2, "2024-12-20", "2024-12-20", 15, "10:08:00", "10:20:00", "595", "", "2024-12-09 10:27:51", "jeje", 5, "url");
INSERT INTO Events values (1, 1, "2024-12-20", "2024-12-20", 15, "11:08:00", "22:23:00", "1", "", "2024-12-10 04:27:51", "gtgtg", 11, "https://example.com/scheduling/event?creator_id=1");
INSERT INTO Events values (49, 2, "2024-12-20", "2024-12-20", 15, "13:08:00", "10:20:00", "49", "", "2024-12-09 10:27:51", "lmlm", 5, "url");
INSERT INTO Events values (101, 2, "2024-12-20", "2024-12-20", 15, "09:08:00", "10:20:00", "101", "", "2024-12-09 10:27:51", "gkgk", 2, "url");
INSERT INTO Events values (2, 1, "2024-12-20", "2024-12-20", 15, "07:08:00", "22:23:00", "2", "", "2024-12-10 04:27:51", "hhaah", 11, "https://example.com/scheduling/event?creator_id=1");
INSERT INTO Events values (3, 1, "2024-12-20", "2024-12-20", 30, "08:08:00", "22:23:00", "3", "", "2024-12-10 04:27:51", "here", 1, "https://example.com/scheduling/event?creator_id=1");
INSERT INTO Events values (4, 2, "2024-12-20", "2024-12-20", 15, "10:08:00", "10:20:00", "101", "", "2024-12-09 10:27:51", "gkgk", 2, "url");
-- bookings
-- 2|1|1|gzu@gmail.com|2|2024-12-09
-- 3|49|1|gzu@gmail.com|2|2024-12-09
-- 4|595|1|gzu@gmail.com|2|2024-12-10
-- 5|101|2|kwang@gmail.com|2|2024-12-09
-- user 1 booked events 2, 3, and 4 (1, 49, 595)
-- user 2 booked event 5, 1 (101, 594)
INSERT INTO Bookings values (2, 1, 1, "gzu@gmail.com", 2, "2024-12-09");
INSERT INTO Bookings values (3, 49, 1, "gzu@gmail.com", 2, "2024-12-09");
INSERT INTO Bookings values (4, 595, 1, "gzu@gmail.com", 2, "2024-12-10");
INSERT INTO Bookings values (5, 101, 2, "kwang@gmail.com", 2, "2024-12-09");
INSERT INTO Bookings values (1, 594, 2, "kwang@gmail.com", 2, "2024-12-09");
-- time requests
-- 1|1|1|2024-12-10|2024-12-10|60|18:53|19:53|llele|1|pending|2024-12-10 23:53:23
-- 2|1|1|2024-12-10|2024-12-10|120|18:54|20:54|haha|1|pending|2024-12-10 23:54:24
-- 3|1|2|2024-12-10|2024-12-10|60|18:53|19:53|ouou|1|pending|2024-12-10 23:53:23
-- 4|2|1|2024-12-10|2024-12-10|60|18:53|19:53|ouou|1|pending|2024-12-10 23:53:23
-- user 1 sent time requests 1 and 2 and received time request 4
-- user 2 received time request 3
INSERT INTO TimeRequests values (1, 1, 1, "2024-12-12", "2024-12-10", 60, "18:53", "19:53", "llele", "1", "pending", "2024-12-10 23:53:23");
INSERT INTO TimeRequests values (2, 1, 1, "2024-12-12", "2024-12-10", 120, "18:54", "20:54", "haha", "594", "pending", "2024-12-10 23:54:24");
INSERT INTO TimeRequests values (3, 1, 2, "2024-12-12", "2024-12-10", 60, "18:53", "19:53", "gcgcg", "2", "pending", "2024-12-10 23:53:23");
INSERT INTO TimeRequests values (4, 1, 2, "2024-12-12", "2024-12-10", 60, "18:53", "19:53", "ththt", "1", "pending", "2024-12-10 23:53:23");
INSERT INTO TimeRequests values (5, 1, 2, "2024-12-12", "2024-12-10", 60, "18:53", "19:53", "nbnbn", "49", "pending", "2024-12-10 23:53:23");

