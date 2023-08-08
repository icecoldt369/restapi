CREATE TABLE teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  sport VARCHAR(255) NOT NULL
);

CREATE TABLE players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  surname VARCHAR(255) NOT NULL,
  given_names VARCHAR(255) NOT NULL,
  nationality VARCHAR(255) NOT NULL,
  date_of_birth DATE NOT NULL,
  FOREIGN KEY (team_id) REFERENCES teams(id)
);


INSERT INTO `players` (`team_id`, `surname`, `given_names`, `nationality`, `date_of_birth`) VALUES (1, 'Arrizabalaga Revuelta', 'Kepa', 'Spain', '1994-10-03'), (1, 'James', 'Reece', 'British', '1999-12-08'), (1, 'Fernández', 'Enzo', 'Argentina', '2001-01-17');

INSERT INTO `players` (`team_id`, `surname`, `given_names`, `nationality`, `date_of_birth`) VALUES (3, 'Becker', 'Alisson', 'Brazil', '1992-10-02'), (3, 'Salah', 'Mohammed', 'Egypt', '1992-06-15'), (3, 'van Dijk', 'Virgil', 'Netherlands', '1991-07-08');

 INSERT INTO `players` (`team_id`, `surname`, `given_names`, `nationality`, `date_of_birth`) VALUES (2, 'Lindelöf', 'Victor', 'Swedish', '1994-07-17'), (2, 'Dalot', 'Diogo', 'Portugese', '1999-03-18'), (2, 'Rashford', 'Marcus', 'British', '1997-10-31');

 INSERT INTO `teams` (`name`, `sport`, `average_age`)  VALUES  ('Chelsea FC', 'football', 26.0), ('Manchester United', 'football', 26.5), ('Liverpool FC', 'football', 27.2);