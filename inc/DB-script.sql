-- Remove the resources column from entries b/c we're creating a linking table
ALTER TABLE entries RENAME to _entries_old;

CREATE TABLE entries (
    id INT PRIMARY KEY,
    title TEXT,
    date TEXT,
    time_spent TEXT,
    learned TEXT
);

INSERT INTO entries (id, title, date, time_spent, learned)
SELECT id, title, date, time_spent, learned
FROM _entries_old;

DROP TABLE _entries_old;
----------------------------------------------------------------

-- Create the resources table
CREATE TABLE resources(
    id INTEGER NOT NULL PRIMARY KEY,
    name VARCHAR(255),
    link VARCHAR(255)
);
----------------------------------------------------------------

-- Create the entry-resources linking table
CREATE TABLE entry_resources(
    entry_id INTEGER NOT NULL,
    resource_id INTEGER NOT NULL,
    UNIQUE(entry_id, resource_id)
    CONSTRAINT fk_column1
    FOREIGN KEY (entry_id)
    REFERENCES entries (id)
    ON DELETE CASCADE,
    CONSTRAINT fk_column2
    FOREIGN KEY (resource_id)
    REFERENCES resources (id)
    ON DELETE CASCADE
);
----------------------------------------------------------------
-- Add Test entries to the resources table
INSERT INTO resources (name, link)
VALUES
("PHP Tutorial","https://www.w3schools.com/php/default.asp"),
("JavaScript Tutorial","https://www.w3schools.com/js/default.asp"),
("HTML Tutorial","https://www.w3schools.com/html/default.asp"),
("CSS Tutorial","https://www.w3schools.com/css/default.asp"),
("SQL Tutorial","https://www.w3schools.com/sql/default.asp"),
("Python Tutorial","https://www.w3schools.com/python/default.asp"),
("Learn Bootstrap","https://www.w3schools.com/bootstrap/bootstrap_ver.asp"),
("jQuery Tutorial","https://www.w3schools.com/jquery/default.asp"),
("Node.js Tutorial","https://www.w3schools.com/nodejs/default.asp"),
("Java Tutorial","https://www.w3schools.com/java/default.asp");

----------------------------------------------------------------
-- Add test entires to entries
INSERT INTO entries (title, date, time_spent, learned)
VALUES
("Lost Dreams","2018-03-16","15 hours","4-6-3 warning track knuckleball fastball perfect game dead red foul line. Swing pennant loogy off-speed ball bench bunt warning track strike zone. No decision outfielder defensive indifference robbed alley mitt fall classic small ball. Bunt pennant pull check swing streak on deck mustard. Cork stretch 4-bagger grass strikeout walk off contact earned run. Tigers hot dog left fielder fastball unearned run, manager full count."),
("The Cracked Return","2018-04-15","17 hours","Robbed cork basehit dead ball era cheese petey outfield national pastime perfect game. Sweep shortstop center fielder strikeout gold glove, defensive indifference tossed. Wins off-speed pinch hitter pine tar cy young bleeder ball. Backstop range first base hit by pitch stretch hardball ground rule double. Cubs bag third base arm left fielder fenway ground ball. Third baseman nubber knuckle rip butcher boy series season loss."),
("Word of Storm","2019-05-23","2 days","Bacon ipsum dolor amet pork loin biltong shankle turducken filet mignon corned beef porchetta salami. Swine short loin buffalo shankle salami filet mignon brisket. Picanha ground round rump jerky brisket. Biltong corned beef ribeye, chislic chicken ham short loin. Turkey burgdoggen fatback porchetta kielbasa, frankfurter pork loin shankle landjaeger shoulder rump."),
("The Destiny's Sword","2020-01-05","45 min","Bacon ipsum dolor amet tri-tip leberkas pork chop doner t-bone, chuck filet mignon sausage picanha beef ribs. Ham hock pig ribeye, ball tip chuck tail venison bresaola spare ribs boudin picanha leberkas pork loin tri-tip. Pork short loin cupim ball tip, kielbasa venison shankle meatloaf jerky ground round filet mignon biltong. Hamburger pork belly t-bone, kevin boudin chicken turducken flank jowl ham brisket kielbasa sirloin. Alcatra shoulder shank, pork capicola t-bone beef fatback sirloin turducken. Sausage salami capicola pastrami hamburger burgdoggen pork belly shoulder tri-tip pork porchetta alcatra ribeye kevin."),
("The Bridge of the Ashes","2020-03-06","85 min","Game petey plunked mitt first baseman mendoza line pitchout pennant. Field plunked losses grand slam inside passed ball no decision tossed. Dodgers run nubber on-base percentage national pastime baseball card umpire starter. Bat inning away center fielder first baseman, crooked number starter fair. Win leadoff friendly confines airmail cracker jack ball starter doubleheader. Foul pinch hitter interleague designated hitter tag knuckle shift glove."),
("Edge in the Spirits","2019-12-15","18 hours","Tossed slugging team starting pitcher hit by pitch passed ball warning track left on base ground ball. Balk game helmet interleague fair runs wild pitch friendly confines bandbox. Slide second base backstop glove bunt lineup skipper extra innings appeal. Lineup practice rake cycle hitter cycle steal alley cy young. Cookie hitter tag tossed crooked number southpaw sport all-star. Center fielder bench assist glove base on balls, all-star ball hot dog."),
("Forgotten Shores","2019-11-14","1 week","Bush league tag baseball card golden sombrero club forkball tapper. Strikeout batting average peanuts error no-hitter visitors 4-bagger cardinals stadium. Stretch tag bench pinch hit steal fair force. No-hitter nubber loogy leadoff disabled list rubber loogy strike zone. No decision manager relay arm earned run game national pastime. Pine tar rainout field fair outfielder, dodgers league hack."),
("The Living Lords","2019-10-14","3 weeks","Third base run rally dodgers fall classic balk range on-base percentage sport. Dribbler season screwball right fielder cookie no decision home stadium. Forkball arm slide suicide squeeze chin music, off-speed streak. Butcher boy outside southpaw sacrifice fly shortstop batting average leadoff astroturf third baseman. Wins gapper bush league pitchout hardball hitter team. 1-2-3 base around the horn yankees full count stadium 4-6-3 base gap."),
("Magic of Stone","2019-08-04","7 hours","Shift mendoza line pitchout 4-6-3 starter retire league warning track baltimore chop. Season baseline center fielder stadium stance center fielder game reds. Tag cup of coffee pull second baseman full count gold glove grounder pull. Sacrifice perfect game knuckleball silver slugger off-speed, slugging red sox. Foul pole swing win home scorecard rookie home gap basehit. Left on base tag around the horn line drive bandbox slugging grand slam all-star wins."),
("The Flame's Illusion","2019-07-17","10 min","Baseball card reds foul pole sabremetrics earned run good eye triple play. Hey batter retire away gap sacrifice forkball mitt cellar cycle. Gold glove line drive series baseball 4-6-3 ball right field cycle. Batter's box squeeze forkball warning track center field bandbox count hey batter unearned run. Third base rubber game outfield rip mustard, grand slam foul pole. Away ejection batting average right field bunt play bat screwball."),
("The School's Years","2017-05-06","55 min","Unearned run outfield 1-2-3 scorecard skipper retire double switch at-bat designated hitter. Mitt fenway gold glove dribbler double play squeeze first baseman assist. Fielder's choice hot dog reliever batter's box around the horn appeal mound. Baseball basehit second base sabremetrics all-star, cookie rookie left on base league. Hot dog away curve defensive indifference bleeder southpaw 1-2-3 steal dead ball era. All-star perfect game error full count squeeze rubber field backstop rope."),
("Shores in the Pirates","2017-06-19","47 min","Baseball around the horn losses inning astroturf, small ball team. Skipper grounder first baseman alley in the hole earned run range. Disabled list good eye cup of coffee warning track yankees bush league bases loaded pinch hit. Gold glove slide mitt sabremetrics rubber hot dog plunked batting average. Knuckle season alley dead red foul red sox cubs. Strike zone third baseman fenway pennant range save bullpen shift plate."),
("Shadowy Years","2017-04-15","18 hours","Bacon ipsum dolor amet porchetta salami chislic, ham meatball burgdoggen capicola spare ribs pork belly. Turkey brisket strip steak chislic, turducken meatloaf beef. Andouille bacon beef turkey filet mignon short ribs kevin pork chop. Short loin t-bone meatball ball tip pork burgdoggen, strip steak meatloaf jerky shank kielbasa. Tongue tenderloin andouille bacon ground round ball tip beef ribs leberkas frankfurter."),
("The Absent Flame","2020-02-25","19 days","Bacon ipsum dolor amet cow porchetta swine drumstick pork chop burgdoggen turkey short loin hamburger t-bone tenderloin venison. Shankle ham hock jowl, pastrami kielbasa picanha pork turkey ribeye doner beef andouille. Turducken leberkas capicola cupim. Jerky ball tip cupim chicken. Venison beef ribs meatloaf chicken, kevin jerky doner pork rump corned beef fatback. Ribeye short loin strip steak kevin rump, buffalo burgdoggen swine capicola drumstick picanha."),
("The Way's Courage","2020-03-16","17 days","Bacon ipsum dolor amet pork loin frankfurter beef salami ball tip filet mignon short ribs buffalo. Bresaola leberkas sirloin alcatra salami. Sirloin andouille spare ribs pastrami strip steak hamburger. Landjaeger sausage meatloaf shankle, pig fatback turkey. Pancetta cow landjaeger leberkas.");

----------------------------------------------------------------
-- Add test entries to entry_resources linking table
INSERT INTO entry_resources (entry_id, resource_id)
VALUES
(1,1),
(3,3),
(4,5),
(5,10),
(6,2),
(7,3),
(8,4),
(9,6),
(10,7),
(11,8),
(12,9),
(13,10),
(14,10),
(15,10),
(16,10),
(17,10),
(18,10),
(19,10),
(19,1),
(18,1),
(17,1),
(16,1),
(15,1),
(14,1),
(13,1),
(12,1),
(11,1),
(10,1),
(9,1),
(8,1),
(7,1),
(6,1),
(5,1),
(4,1),
(3,1),
(2,1);
