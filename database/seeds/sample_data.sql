-- ============================================================
-- QuizArena — Sample Data Seed
-- 15 quizzes, 6 categories, 10+ questions each
-- Requires: player_one user from schema.sql
-- ============================================================

-- Helper: we'll reference player_one's UUID via subquery
-- Run this after schema.sql

DO $$
DECLARE
    uid        UUID;
    qz_id      UUID;
    q_id       UUID;

BEGIN

-- Get seeder user (player_one from schema)
SELECT id INTO uid FROM users WHERE username = 'player_one';
IF uid IS NULL THEN
    RAISE EXCEPTION 'player_one user not found — run schema.sql first';
END IF;

-- ============================================================
-- GEOGRAPHY
-- ============================================================

-- Quiz 1: World Capitals
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'World Capitals', 'Geography', 1)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'What is the capital of France?',        0, 20, 0),
(uuid_generate_v4(), qz_id, 'What is the capital of Japan?',         1, 20, 1),
(uuid_generate_v4(), qz_id, 'What is the capital of Brazil?',        2, 20, 2),
(uuid_generate_v4(), qz_id, 'What is the capital of Australia?',     3, 20, 3),
(uuid_generate_v4(), qz_id, 'What is the capital of Canada?',        0, 20, 4),
(uuid_generate_v4(), qz_id, 'What is the capital of Egypt?',         1, 20, 5),
(uuid_generate_v4(), qz_id, 'What is the capital of Argentina?',     2, 20, 6),
(uuid_generate_v4(), qz_id, 'What is the capital of South Korea?',   0, 20, 7),
(uuid_generate_v4(), qz_id, 'What is the capital of Nigeria?',       3, 20, 8),
(uuid_generate_v4(), qz_id, 'What is the capital of India?',         1, 20, 9);

-- Answers for Quiz 1
INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0, 0, 'Paris'),      (0, 1, 'Lyon'),       (0, 2, 'Marseille'),  (0, 3, 'Nice'),
  (1, 0, 'Osaka'),      (1, 1, 'Tokyo'),      (1, 2, 'Kyoto'),      (1, 3, 'Hiroshima'),
  (2, 0, 'São Paulo'),  (2, 1, 'Rio de Janeiro'),(2, 2, 'Brasília'),(2, 3, 'Salvador'),
  (3, 0, 'Sydney'),     (3, 1, 'Melbourne'),  (3, 2, 'Brisbane'),   (3, 3, 'Canberra'),
  (4, 0, 'Ottawa'),     (4, 1, 'Toronto'),    (4, 2, 'Vancouver'),  (4, 3, 'Montreal'),
  (5, 0, 'Alexandria'), (5, 1, 'Cairo'),      (5, 2, 'Luxor'),      (5, 3, 'Giza'),
  (6, 0, 'Córdoba'),    (6, 1, 'Rosario'),    (6, 2, 'Buenos Aires'),(6, 3, 'Mendoza'),
  (7, 0, 'Seoul'),      (7, 1, 'Busan'),      (7, 2, 'Incheon'),    (7, 3, 'Daegu'),
  (8, 0, 'Lagos'),      (8, 1, 'Kano'),       (8, 2, 'Ibadan'),     (8, 3, 'Abuja'),
  (9, 0, 'Mumbai'),     (9, 1, 'New Delhi'),  (9, 2, 'Kolkata'),    (9, 3, 'Chennai')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 2: Oceans & Seas
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Oceans & Seas', 'Geography', 2)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'Which is the largest ocean on Earth?',              0, 25, 0),
(uuid_generate_v4(), qz_id, 'Which ocean is the smallest?',                      2, 25, 1),
(uuid_generate_v4(), qz_id, 'The Mariana Trench is located in which ocean?',     0, 25, 2),
(uuid_generate_v4(), qz_id, 'Which sea is the saltiest in the world?',           1, 25, 3),
(uuid_generate_v4(), qz_id, 'Which ocean borders Europe to the west?',           3, 25, 4),
(uuid_generate_v4(), qz_id, 'What is the deepest lake in the world?',            2, 25, 5),
(uuid_generate_v4(), qz_id, 'Which strait connects the Atlantic and Pacific?',   1, 25, 6),
(uuid_generate_v4(), qz_id, 'The Great Barrier Reef is located off which country?', 0, 25, 7),
(uuid_generate_v4(), qz_id, 'Which river is the longest in the world?',          1, 25, 8),
(uuid_generate_v4(), qz_id, 'The Caspian Sea borders how many countries?',       3, 25, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0, 0,'Pacific'),(0,1,'Atlantic'),(0,2,'Indian'),(0,3,'Arctic'),
  (1, 0,'Indian'),(1,1,'Atlantic'),(1,2,'Arctic'),(1,3,'Pacific'),
  (2, 0,'Pacific'),(2,1,'Atlantic'),(2,2,'Indian'),(2,3,'Arctic'),
  (3, 0,'Caspian Sea'),(3,1,'Dead Sea'),(3,2,'Red Sea'),(3,3,'Salton Sea'),
  (4, 0,'Indian'),(4,1,'Pacific'),(4,2,'Arctic'),(4,3,'Atlantic'),
  (5, 0,'Titicaca'),(5,1,'Superior'),(5,2,'Baikal'),(5,3,'Caspian'),
  (6, 0,'Gibraltar'),(6,1,'Drake Passage'),(6,2,'Bosphorus'),(6,3,'Magellan'),
  (7, 0,'Australia'),(7,1,'New Zealand'),(7,2,'Indonesia'),(7,3,'Philippines'),
  (8, 0,'Amazon'),(8,1,'Nile'),(8,2,'Yangtze'),(8,3,'Mississippi'),
  (9, 0,'3'),(9,1,'4'),(9,2,'6'),(9,3,'5')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- ============================================================
-- SCIENCE
-- ============================================================

-- Quiz 3: Physics Basics
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Physics Basics', 'Science', 1)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'What is the speed of light (approx.) in km/s?',      2, 30, 0),
(uuid_generate_v4(), qz_id, 'What unit measures electrical resistance?',           1, 25, 1),
(uuid_generate_v4(), qz_id, 'Who formulated the law of universal gravitation?',    0, 25, 2),
(uuid_generate_v4(), qz_id, 'What is the chemical formula for water?',             3, 20, 3),
(uuid_generate_v4(), qz_id, 'What particle has a negative charge?',                2, 20, 4),
(uuid_generate_v4(), qz_id, 'What is absolute zero in Celsius?',                  1, 30, 5),
(uuid_generate_v4(), qz_id, 'Energy = mass × speed of light². Whose formula?',    0, 25, 6),
(uuid_generate_v4(), qz_id, 'What is the SI unit of force?',                      3, 20, 7),
(uuid_generate_v4(), qz_id, 'Sound travels fastest through which medium?',         2, 25, 8),
(uuid_generate_v4(), qz_id, 'What type of wave is light?',                        1, 25, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'150,000'),(0,1,'200,000'),(0,2,'300,000'),(0,3,'400,000'),
  (1,0,'Ampere'),(1,1,'Ohm'),(1,2,'Volt'),(1,3,'Watt'),
  (2,0,'Isaac Newton'),(2,1,'Albert Einstein'),(2,2,'Galileo Galilei'),(2,3,'Nikola Tesla'),
  (3,0,'CO2'),(3,1,'H2O2'),(3,2,'HO'),(3,3,'H2O'),
  (4,0,'Proton'),(4,1,'Neutron'),(4,2,'Electron'),(4,3,'Positron'),
  (5,0,'0'),(5,1,'-273.15'),(5,2,'-300'),(5,3,'-373'),
  (6,0,'Einstein'),(6,1,'Newton'),(6,2,'Bohr'),(6,3,'Planck'),
  (7,0,'Joule'),(7,1,'Watt'),(7,2,'Pascal'),(7,3,'Newton'),
  (8,0,'Gas'),(8,1,'Vacuum'),(8,2,'Solid'),(8,3,'Liquid'),
  (9,0,'Mechanical'),(9,1,'Electromagnetic'),(9,2,'Sound'),(9,3,'Transverse only')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 4: Human Body
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Human Body', 'Science', 2)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'How many bones are in the adult human body?',         1, 25, 0),
(uuid_generate_v4(), qz_id, 'Which organ produces insulin?',                       2, 20, 1),
(uuid_generate_v4(), qz_id, 'What is the largest organ of the human body?',        0, 20, 2),
(uuid_generate_v4(), qz_id, 'How many chambers does the human heart have?',        3, 20, 3),
(uuid_generate_v4(), qz_id, 'Which blood type is the universal donor?',            1, 25, 4),
(uuid_generate_v4(), qz_id, 'What is the longest bone in the human body?',         2, 25, 5),
(uuid_generate_v4(), qz_id, 'Which part of the brain controls balance?',           0, 30, 6),
(uuid_generate_v4(), qz_id, 'How many teeth does an adult human have?',            3, 20, 7),
(uuid_generate_v4(), qz_id, 'What carries oxygen in red blood cells?',             1, 25, 8),
(uuid_generate_v4(), qz_id, 'The patella is commonly known as the…',              2, 20, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'180'),(0,1,'206'),(0,2,'212'),(0,3,'198'),
  (1,0,'Liver'),(1,1,'Kidney'),(1,2,'Pancreas'),(1,3,'Spleen'),
  (2,0,'Skin'),(2,1,'Liver'),(2,2,'Lung'),(2,3,'Intestine'),
  (3,0,'2'),(3,1,'3'),(3,2,'5'),(3,3,'4'),
  (4,0,'AB+'),(4,1,'O-'),(4,2,'A+'),(4,3,'B-'),
  (5,0,'Humerus'),(5,1,'Tibia'),(5,2,'Femur'),(5,3,'Fibula'),
  (6,0,'Cerebellum'),(6,1,'Cerebrum'),(6,2,'Medulla'),(6,3,'Amygdala'),
  (7,0,'28'),(7,1,'30'),(7,2,'36'),(7,3,'32'),
  (8,0,'Plasma'),(8,1,'Hemoglobin'),(8,2,'Platelets'),(8,3,'Leukocytes'),
  (9,0,'Elbow cap'),(9,1,'Shinbone'),(9,2,'Kneecap'),(9,3,'Shoulder blade')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 5: Space & Astronomy
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Space & Astronomy', 'Science', 3)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'How many planets are in our solar system?',           2, 20, 0),
(uuid_generate_v4(), qz_id, 'Which planet is closest to the Sun?',                 0, 20, 1),
(uuid_generate_v4(), qz_id, 'What is the name of our galaxy?',                    3, 20, 2),
(uuid_generate_v4(), qz_id, 'Which planet has the most moons?',                   1, 25, 3),
(uuid_generate_v4(), qz_id, 'What is a light-year a measure of?',                 2, 25, 4),
(uuid_generate_v4(), qz_id, 'Who was the first human to walk on the Moon?',       0, 25, 5),
(uuid_generate_v4(), qz_id, 'What type of star is our Sun?',                      1, 30, 6),
(uuid_generate_v4(), qz_id, 'Which planet is known as the Red Planet?',           3, 20, 7),
(uuid_generate_v4(), qz_id, 'What force keeps planets in orbit?',                 2, 25, 8),
(uuid_generate_v4(), qz_id, 'The Big Bang occurred approximately how long ago?',  0, 30, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'7'),(0,1,'9'),(0,2,'8'),(0,3,'10'),
  (1,0,'Mercury'),(1,1,'Venus'),(1,2,'Earth'),(1,3,'Mars'),
  (2,0,'Andromeda'),(2,1,'Triangulum'),(2,2,'Sombrero'),(2,3,'Milky Way'),
  (3,0,'Jupiter'),(3,1,'Saturn'),(3,2,'Uranus'),(3,3,'Neptune'),
  (4,0,'Time'),(4,1,'Speed'),(4,2,'Distance'),(4,3,'Mass'),
  (5,0,'Neil Armstrong'),(5,1,'Buzz Aldrin'),(5,2,'Yuri Gagarin'),(5,3,'John Glenn'),
  (6,0,'Red giant'),(6,1,'Yellow dwarf'),(6,2,'White dwarf'),(6,3,'Neutron star'),
  (7,0,'Venus'),(7,1,'Jupiter'),(7,2,'Saturn'),(7,3,'Mars'),
  (8,0,'Magnetism'),(8,1,'Friction'),(8,2,'Gravity'),(8,3,'Nuclear force'),
  (9,0,'13.8 billion years ago'),(9,1,'4.5 billion years ago'),(9,2,'20 billion years ago'),(9,3,'6,000 years ago')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- ============================================================
-- HISTORY
-- ============================================================

-- Quiz 6: Ancient Civilizations
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Ancient Civilizations', 'History', 2)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'Which civilization built the pyramids of Giza?',      0, 25, 0),
(uuid_generate_v4(), qz_id, 'The Colosseum is located in which city?',             2, 20, 1),
(uuid_generate_v4(), qz_id, 'Who was the first Emperor of Rome?',                  1, 30, 2),
(uuid_generate_v4(), qz_id, 'The Great Wall of China was primarily built to defend against whom?', 3, 30, 3),
(uuid_generate_v4(), qz_id, 'Which ancient wonder was located in Alexandria?',     2, 30, 4),
(uuid_generate_v4(), qz_id, 'The Aztec Empire was conquered by which explorer?',   0, 30, 5),
(uuid_generate_v4(), qz_id, 'Where was Alexander the Great born?',                 1, 30, 6),
(uuid_generate_v4(), qz_id, 'Which empire was ruled by Julius Caesar?',            3, 25, 7),
(uuid_generate_v4(), qz_id, 'In which century did the Western Roman Empire fall?', 2, 30, 8),
(uuid_generate_v4(), qz_id, 'The Parthenon was built to honor which goddess?',     0, 30, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'Egyptians'),(0,1,'Romans'),(0,2,'Greeks'),(0,3,'Mesopotamians'),
  (1,0,'Athens'),(1,1,'Naples'),(1,2,'Rome'),(1,3,'Florence'),
  (2,0,'Julius Caesar'),(2,1,'Augustus'),(2,2,'Nero'),(2,3,'Trajan'),
  (3,0,'Romans'),(3,1,'Persians'),(3,2,'Japanese'),(3,3,'Mongols'),
  (4,0,'Hanging Gardens'),(4,1,'Colossus of Rhodes'),(4,2,'Lighthouse of Alexandria'),(4,3,'Temple of Artemis'),
  (5,0,'Hernán Cortés'),(5,1,'Francisco Pizarro'),(5,2,'Christopher Columbus'),(5,3,'Ferdinand Magellan'),
  (6,0,'Athens'),(6,1,'Pella, Macedonia'),(6,2,'Sparta'),(6,3,'Corinth'),
  (7,0,'Greek'),(7,1,'Byzantine'),(7,2,'Ottoman'),(7,3,'Roman'),
  (8,0,'3rd century'),(8,1,'4th century'),(8,2,'5th century'),(8,3,'6th century'),
  (9,0,'Athena'),(9,1,'Aphrodite'),(9,2,'Hera'),(9,3,'Artemis')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 7: World War II
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'World War II', 'History', 3)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'In which year did World War II begin?',                0, 20, 0),
(uuid_generate_v4(), qz_id, 'Which event brought the USA into WWII?',               2, 25, 1),
(uuid_generate_v4(), qz_id, 'What was the code name for the D-Day invasion?',       1, 30, 2),
(uuid_generate_v4(), qz_id, 'Which country suffered the most casualties in WWII?',  3, 30, 3),
(uuid_generate_v4(), qz_id, 'Where were atomic bombs dropped in 1945?',             0, 25, 4),
(uuid_generate_v4(), qz_id, 'Who was the British Prime Minister during most of WWII?', 1, 25, 5),
(uuid_generate_v4(), qz_id, 'What was the name of the Nazi secret police?',         2, 30, 6),
(uuid_generate_v4(), qz_id, 'The Battle of Stalingrad was fought between Germany and…', 3, 30, 7),
(uuid_generate_v4(), qz_id, 'What year did WWII end?',                              1, 20, 8),
(uuid_generate_v4(), qz_id, 'Which alliance opposed the Axis powers?',              0, 25, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'1939'),(0,1,'1940'),(0,2,'1941'),(0,3,'1938'),
  (1,0,'Sinking of Lusitania'),(1,1,'Berlin blockade'),(1,2,'Attack on Pearl Harbor'),(1,3,'D-Day'),
  (2,0,'Operation Overlord'),(2,1,'Operation Neptune'),(2,2,'Operation Barbarossa'),(2,3,'Operation Market Garden'),
  (3,0,'Germany'),(3,1,'Japan'),(3,2,'France'),(3,3,'Soviet Union'),
  (4,0,'Hiroshima and Nagasaki'),(4,1,'Tokyo and Osaka'),(4,2,'Berlin and Munich'),(4,3,'Kyoto and Nagasaki'),
  (5,0,'Neville Chamberlain'),(5,1,'Winston Churchill'),(5,2,'Clement Attlee'),(5,3,'Anthony Eden'),
  (6,0,'Wehrmacht'),(6,1,'Luftwaffe'),(6,2,'Gestapo'),(6,3,'SS'),
  (7,0,'France'),(7,1,'UK'),(7,2,'USA'),(7,3,'Soviet Union'),
  (8,0,'1944'),(8,1,'1945'),(8,2,'1946'),(8,3,'1943'),
  (9,0,'The Allies'),(9,1,'The Entente'),(9,2,'The Coalition'),(9,3,'The Allied Powers')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 8: Modern History
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Modern History', 'History', 2)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'In which year did the Berlin Wall fall?',             2, 20, 0),
(uuid_generate_v4(), qz_id, 'Who was the first person to walk on the Moon?',       0, 20, 1),
(uuid_generate_v4(), qz_id, 'The Cold War was primarily between the USA and…',     1, 20, 2),
(uuid_generate_v4(), qz_id, 'In which year did the Soviet Union dissolve?',        3, 25, 3),
(uuid_generate_v4(), qz_id, 'Nelson Mandela was the president of which country?',  2, 20, 4),
(uuid_generate_v4(), qz_id, 'The 9/11 attacks occurred in which year?',            0, 20, 5),
(uuid_generate_v4(), qz_id, 'Which country first landed a rover on Mars?',         1, 30, 6),
(uuid_generate_v4(), qz_id, 'The Euro currency was introduced in which year?',     3, 30, 7),
(uuid_generate_v4(), qz_id, 'Who invented the World Wide Web?',                    2, 25, 8),
(uuid_generate_v4(), qz_id, 'The Chernobyl disaster occurred in which country?',   0, 25, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'1987'),(0,1,'1990'),(0,2,'1989'),(0,3,'1991'),
  (1,0,'Neil Armstrong'),(1,1,'Buzz Aldrin'),(1,2,'Yuri Gagarin'),(1,3,'John Glenn'),
  (2,0,'China'),(2,1,'Soviet Union'),(2,2,'UK'),(2,3,'Germany'),
  (3,0,'1989'),(3,1,'1990'),(3,2,'1992'),(3,3,'1991'),
  (4,0,'Zimbabwe'),(4,1,'Kenya'),(4,2,'South Africa'),(4,3,'Nigeria'),
  (5,0,'2001'),(5,1,'2002'),(5,2,'2000'),(5,3,'1999'),
  (6,0,'Soviet Union'),(6,1,'USA'),(6,2,'China'),(6,3,'ESA'),
  (7,0,'1995'),(7,1,'1998'),(7,2,'2000'),(7,3,'1999'),
  (8,0,'Bill Gates'),(8,1,'Steve Jobs'),(8,2,'Tim Berners-Lee'),(8,3,'Vint Cerf'),
  (9,0,'Ukraine (USSR)'),(9,1,'Russia'),(9,2,'Belarus'),(9,3,'Poland')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- ============================================================
-- TECHNOLOGY
-- ============================================================

-- Quiz 9: Computer Science Basics
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Computer Science Basics', 'Technology', 1)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'What does CPU stand for?',                           0, 20, 0),
(uuid_generate_v4(), qz_id, 'Which data structure uses LIFO order?',              2, 20, 1),
(uuid_generate_v4(), qz_id, 'What is the binary representation of decimal 10?',   1, 25, 2),
(uuid_generate_v4(), qz_id, 'What does HTML stand for?',                          3, 20, 3),
(uuid_generate_v4(), qz_id, 'Which language is primarily used for styling web pages?', 1, 20, 4),
(uuid_generate_v4(), qz_id, 'What does RAM stand for?',                           0, 20, 5),
(uuid_generate_v4(), qz_id, 'Which sorting algorithm has O(n log n) average complexity?', 2, 30, 6),
(uuid_generate_v4(), qz_id, 'What does SQL stand for?',                           3, 20, 7),
(uuid_generate_v4(), qz_id, 'Which protocol is used to send emails?',             1, 25, 8),
(uuid_generate_v4(), qz_id, 'What is the time complexity of binary search?',      2, 25, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'Central Processing Unit'),(0,1,'Core Power Unit'),(0,2,'Central Power Usage'),(0,3,'Computed Process Unit'),
  (1,0,'Queue'),(1,1,'Array'),(1,2,'Stack'),(1,3,'Linked List'),
  (2,0,'1100'),(2,1,'1010'),(2,2,'1001'),(2,3,'1110'),
  (3,0,'Hyperlinks and Text Markup Language'),(3,1,'High Transfer Markup Language'),(3,2,'Hyper Text Markup Language v2'),(3,3,'HyperText Markup Language'),
  (4,0,'JavaScript'),(4,1,'CSS'),(4,2,'PHP'),(4,3,'XML'),
  (5,0,'Random Access Memory'),(5,1,'Read Access Memory'),(5,2,'Rapid Access Module'),(5,3,'Read And Modify'),
  (6,0,'Bubble Sort'),(6,1,'Selection Sort'),(6,2,'Merge Sort'),(6,3,'Insertion Sort'),
  (7,0,'Simple Query Language'),(7,1,'Sequential Query Language'),(7,2,'Standard Query Logic'),(7,3,'Structured Query Language'),
  (8,0,'HTTP'),(8,1,'SMTP'),(8,2,'FTP'),(8,3,'POP3'),
  (9,0,'O(n)'),(9,1,'O(n²)'),(9,2,'O(log n)'),(9,3,'O(1)')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 10: Tech Giants & Innovations
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Tech Giants & Innovations', 'Technology', 2)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'In which year was Apple Inc. founded?',               1, 25, 0),
(uuid_generate_v4(), qz_id, 'Who co-founded Microsoft with Bill Gates?',           0, 25, 1),
(uuid_generate_v4(), qz_id, 'Which company developed the Android OS?',             2, 20, 2),
(uuid_generate_v4(), qz_id, 'What does "AI" stand for in computing?',             3, 20, 3),
(uuid_generate_v4(), qz_id, 'Which company created the PlayStation?',              1, 20, 4),
(uuid_generate_v4(), qz_id, 'What programming language was created by Guido van Rossum?', 2, 25, 5),
(uuid_generate_v4(), qz_id, 'Which company owns YouTube?',                         0, 20, 6),
(uuid_generate_v4(), qz_id, 'What does "IoT" stand for?',                         3, 25, 7),
(uuid_generate_v4(), qz_id, 'Who founded Tesla Motors?',                           1, 25, 8),
(uuid_generate_v4(), qz_id, 'Which language runs natively in web browsers?',      2, 20, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'1972'),(0,1,'1976'),(0,2,'1980'),(0,3,'1984'),
  (1,0,'Paul Allen'),(1,1,'Steve Jobs'),(1,2,'Steve Wozniak'),(1,3,'Larry Page'),
  (2,0,'Apple'),(2,1,'Microsoft'),(2,2,'Google'),(2,3,'Samsung'),
  (3,0,'Automated Interface'),(3,1,'Autonomous Intelligence'),(3,2,'Algorithmic Integration'),(3,3,'Artificial Intelligence'),
  (4,0,'Microsoft'),(4,1,'Sony'),(4,2,'Nintendo'),(4,3,'Sega'),
  (5,0,'Ruby'),(5,1,'Java'),(5,2,'Python'),(5,3,'Perl'),
  (6,0,'Google'),(6,1,'Meta'),(6,2,'Amazon'),(6,3,'Microsoft'),
  (7,0,'Integration of Technology'),(7,1,'Internet of Transactions'),(7,2,'Interconnected Tech'),(7,3,'Internet of Things'),
  (8,0,'Bill Gates'),(8,1,'Elon Musk'),(8,2,'Jeff Bezos'),(8,3,'Martin Eberhard'),
  (9,0,'Python'),(9,1,'PHP'),(9,2,'JavaScript'),(9,3,'TypeScript')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- ============================================================
-- MATH
-- ============================================================

-- Quiz 11: Math Fundamentals
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Math Fundamentals', 'Math', 1)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'What is the value of π (pi) to 2 decimal places?',   0, 20, 0),
(uuid_generate_v4(), qz_id, 'What is the square root of 144?',                    2, 20, 1),
(uuid_generate_v4(), qz_id, 'What is 15% of 200?',                                1, 25, 2),
(uuid_generate_v4(), qz_id, 'What is the next prime number after 7?',             3, 20, 3),
(uuid_generate_v4(), qz_id, 'What is 2 to the power of 10?',                      2, 25, 4),
(uuid_generate_v4(), qz_id, 'What is the sum of angles in a triangle?',            0, 20, 5),
(uuid_generate_v4(), qz_id, 'What is the factorial of 5 (5!)?',                   1, 25, 6),
(uuid_generate_v4(), qz_id, 'Solve: 3x = 27. What is x?',                        3, 20, 7),
(uuid_generate_v4(), qz_id, 'What is the area of a circle with radius 5? (π≈3.14)', 2, 30, 8),
(uuid_generate_v4(), qz_id, 'What is the Fibonacci sequence start?',              0, 20, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'3.14'),(0,1,'3.12'),(0,2,'3.16'),(0,3,'3.18'),
  (1,0,'10'),(1,1,'11'),(1,2,'12'),(1,3,'13'),
  (2,0,'25'),(2,1,'30'),(2,2,'35'),(2,3,'40'),
  (3,0,'9'),(3,1,'10'),(3,2,'12'),(3,3,'11'),
  (4,0,'512'),(4,1,'256'),(4,2,'1024'),(4,3,'2048'),
  (5,0,'180°'),(5,1,'90°'),(5,2,'270°'),(5,3,'360°'),
  (6,0,'60'),(6,1,'120'),(6,2,'24'),(6,3,'720'),
  (7,0,'6'),(7,1,'8'),(7,2,'10'),(7,3,'9'),
  (8,0,'62.8'),(8,1,'31.4'),(8,2,'78.5'),(8,3,'50.24'),
  (9,0,'0, 1, 1, 2, 3'),(9,1,'1, 2, 3, 5, 8'),(9,2,'0, 1, 2, 3, 5'),(9,3,'1, 1, 2, 4, 8')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 12: Advanced Math
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Advanced Math', 'Math', 3)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'What is the derivative of x²?',                      1, 30, 0),
(uuid_generate_v4(), qz_id, 'What is log₁₀(1000)?',                              2, 30, 1),
(uuid_generate_v4(), qz_id, 'Which of these is an irrational number?',            0, 25, 2),
(uuid_generate_v4(), qz_id, 'What is the integral of 2x dx?',                    3, 30, 3),
(uuid_generate_v4(), qz_id, 'In a right triangle, if a=3, b=4, what is c?',      2, 25, 4),
(uuid_generate_v4(), qz_id, 'What is the determinant of [[1,2],[3,4]]?',          1, 30, 5),
(uuid_generate_v4(), qz_id, 'What does the symbol ∑ (sigma) represent?',         0, 25, 6),
(uuid_generate_v4(), qz_id, 'Euler''s number e is approximately…',               3, 25, 7),
(uuid_generate_v4(), qz_id, 'What is sin(90°)?',                                  2, 20, 8),
(uuid_generate_v4(), qz_id, 'How many sides does a dodecagon have?',              1, 20, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'x'),(0,1,'2x'),(0,2,'x²'),(0,3,'2'),
  (1,0,'2'),(1,1,'10'),(1,2,'3'),(1,3,'100'),
  (2,0,'√2'),(2,1,'1/3'),(2,2,'0.5'),(2,3,'4/2'),
  (3,0,'x'),(3,1,'2x²'),(3,2,'x² + C'),(3,3,'x² + C'),
  (4,0,'6'),(4,1,'6'),(4,2,'5'),(4,3,'7'),
  (5,0,'10'),(5,1,'-2'),(5,2,'2'),(5,3,'-10'),
  (6,0,'Summation'),(6,1,'Product'),(6,2,'Integral'),(6,3,'Difference'),
  (7,0,'2.14'),(7,1,'3.14'),(7,2,'1.62'),(7,3,'2.718'),
  (8,0,'0'),(8,1,'0.5'),(8,2,'1'),(8,3,'-1'),
  (9,0,'10'),(9,1,'12'),(9,2,'8'),(9,3,'14')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- ============================================================
-- MOVIES
-- ============================================================

-- Quiz 13: Classic Movies
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Classic Movies', 'Movies', 1)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'Who directed "Schindler''s List"?',                  0, 25, 0),
(uuid_generate_v4(), qz_id, 'Which film won the first Academy Award for Best Picture?', 2, 30, 1),
(uuid_generate_v4(), qz_id, '"Here''s looking at you, kid" is from which film?',  1, 25, 2),
(uuid_generate_v4(), qz_id, 'In "The Godfather", what animal head is found in the bed?', 3, 25, 3),
(uuid_generate_v4(), qz_id, 'Who played James Bond in "Casino Royale" (2006)?',   2, 20, 4),
(uuid_generate_v4(), qz_id, 'Which film features the character "Hannibal Lecter"?', 0, 25, 5),
(uuid_generate_v4(), qz_id, 'What year was "Titanic" (1997) released?',           3, 20, 6),
(uuid_generate_v4(), qz_id, '"May the Force be with you" is from which franchise?', 1, 20, 7),
(uuid_generate_v4(), qz_id, 'Who directed "Pulp Fiction"?',                       2, 25, 8),
(uuid_generate_v4(), qz_id, 'Which actor played "The Joker" in The Dark Knight?', 0, 20, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'Steven Spielberg'),(0,1,'Martin Scorsese'),(0,2,'Francis Ford Coppola'),(0,3,'Stanley Kubrick'),
  (1,0,'Gone with the Wind'),(1,1,'Citizen Kane'),(1,2,'Wings'),(1,3,'Casablanca'),
  (2,0,'Gone with the Wind'),(2,1,'Casablanca'),(2,2,'Citizen Kane'),(2,3,'Sunset Boulevard'),
  (3,0,'Dog'),(3,1,'Cat'),(3,2,'Pig'),(3,3,'Horse'),
  (4,0,'Pierce Brosnan'),(4,1,'Roger Moore'),(4,2,'Daniel Craig'),(4,3,'Sean Connery'),
  (5,0,'The Silence of the Lambs'),(5,1,'Se7en'),(5,2,'Zodiac'),(5,3,'Hannibal'),
  (6,0,'1995'),(6,1,'1996'),(6,2,'1998'),(6,3,'1997'),
  (7,0,'Star Trek'),(7,1,'Star Wars'),(7,2,'Dune'),(7,3,'Guardians of the Galaxy'),
  (8,0,'Martin Scorsese'),(8,1,'Stanley Kubrick'),(8,2,'Quentin Tarantino'),(8,3,'Ridley Scott'),
  (9,0,'Heath Ledger'),(9,1,'Jack Nicholson'),(9,2,'Joaquin Phoenix'),(9,3,'Jared Leto')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 14: Sci-Fi & Fantasy Films
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Sci-Fi & Fantasy Films', 'Movies', 2)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'In "The Matrix", which pill does Neo take?',          0, 20, 0),
(uuid_generate_v4(), qz_id, 'Who created the One Ring in Lord of the Rings?',      2, 25, 1),
(uuid_generate_v4(), qz_id, 'What is the name of the spaceship in "Alien"?',      1, 30, 2),
(uuid_generate_v4(), qz_id, 'In "Interstellar", what is the name of the wormhole planet?', 3, 30, 3),
(uuid_generate_v4(), qz_id, 'Who plays Iron Man in the MCU?',                      0, 20, 4),
(uuid_generate_v4(), qz_id, 'What planet is Superman from?',                       2, 20, 5),
(uuid_generate_v4(), qz_id, '"I''ll be back" is said by which character?',         1, 20, 6),
(uuid_generate_v4(), qz_id, 'In "Avatar", what is the name of the moon?',         3, 25, 7),
(uuid_generate_v4(), qz_id, 'Which Hogwarts house is Harry Potter sorted into?',  2, 20, 8),
(uuid_generate_v4(), qz_id, 'In "Blade Runner", what are replicants?',            0, 30, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'Red'),(0,1,'Blue'),(0,2,'Green'),(0,3,'Yellow'),
  (1,0,'Gandalf'),(1,1,'Aragorn'),(1,2,'Sauron'),(1,3,'Saruman'),
  (2,0,'Discovery'),(2,1,'Nostromo'),(2,2,'Sulaco'),(2,3,'Prometheus'),
  (3,0,'Gargantua'),(3,1,'Endurance'),(3,2,'Lazarus'),(3,3,'Miller''s Planet'),
  (4,0,'Robert Downey Jr.'),(4,1,'Chris Evans'),(4,2,'Mark Ruffalo'),(4,3,'Chris Hemsworth'),
  (5,0,'Earth'),(5,1,'Mars'),(5,2,'Krypton'),(5,3,'Oa'),
  (6,0,'RoboCop'),(6,1,'The Terminator'),(6,2,'Darth Vader'),(6,3,'Predator'),
  (7,0,'Titan'),(7,1,'Endor'),(7,2,'Pandora Prime'),(7,3,'Pandora'),
  (8,0,'Slytherin'),(8,1,'Ravenclaw'),(8,2,'Gryffindor'),(8,3,'Hufflepuff'),
  (9,0,'Androids'),(9,1,'Aliens'),(9,2,'Mutants'),(9,3,'Cyborgs')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

-- Quiz 15: Movie Awards & Records
INSERT INTO quizzes (id, user_id, title, category, difficulty)
VALUES (uuid_generate_v4(), uid, 'Movie Awards & Records', 'Movies', 3)
RETURNING id INTO qz_id;

INSERT INTO questions (id, quiz_id, content, correct_answer, time_limit, order_index) VALUES
(uuid_generate_v4(), qz_id, 'Which film has won the most Academy Awards?',         2, 30, 0),
(uuid_generate_v4(), qz_id, 'Who has won the most acting Oscars?',                 1, 30, 1),
(uuid_generate_v4(), qz_id, 'Which was the first film to gross $1 billion?',       0, 30, 2),
(uuid_generate_v4(), qz_id, 'What is the highest-grossing film of all time (as of 2024)?', 3, 30, 3),
(uuid_generate_v4(), qz_id, 'Which director has the most Oscar nominations?',      2, 30, 4),
(uuid_generate_v4(), qz_id, 'The Palme d''Or is awarded at which film festival?',  1, 25, 5),
(uuid_generate_v4(), qz_id, 'Which film was the first to use CGI as a major effect?', 3, 30, 6),
(uuid_generate_v4(), qz_id, 'Which actor has appeared in the most films?',         0, 30, 7),
(uuid_generate_v4(), qz_id, 'What was the first feature-length animated film?',    2, 30, 8),
(uuid_generate_v4(), qz_id, 'Which country produces the most films annually?',     1, 25, 9);

INSERT INTO answers (question_id, content, index)
SELECT q.id, a.content, a.idx FROM questions q
JOIN (VALUES
  (0,0,'Gone with the Wind'),(0,1,'Schindler''s List'),(0,2,'Ben-Hur / Titanic / The Lord of the Rings: Return'),(0,3,'Avatar'),
  (1,0,'Jack Nicholson'),(1,1,'Meryl Streep'),(1,2,'Katharine Hepburn'),(1,3,'Cate Blanchett'),
  (2,0,'Jurassic Park'),(2,1,'E.T.'),(2,2,'Star Wars'),(2,3,'Titanic'),
  (3,0,'Avengers: Endgame'),(3,1,'Titanic'),(3,2,'Star Wars: The Force Awakens'),(3,3,'Avatar'),
  (4,0,'Steven Spielberg'),(4,1,'Martin Scorsese'),(4,2,'William Wyler'),(4,3,'Billy Wilder'),
  (5,0,'Venice'),(5,1,'Cannes'),(5,2,'Berlin'),(5,3,'Toronto'),
  (6,0,'Star Wars'),(6,1,'Jurassic Park'),(6,2,'Tron'),(6,3,'The Abyss'),
  (7,0,'John Carradine'),(7,1,'Christopher Lee'),(7,2,'Samuel L. Jackson'),(7,3,'Robert De Niro'),
  (8,0,'Fantasia'),(8,1,'Bambi'),(8,2,'Snow White and the Seven Dwarfs'),(8,3,'Cinderella'),
  (9,0,'USA'),(9,1,'India'),(9,2,'China'),(9,3,'Nigeria')
) AS a(order_idx, idx, content) ON q.order_index = a.order_idx
WHERE q.quiz_id = qz_id;

END $$;