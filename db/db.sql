CREATE TABLE campaigns
(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name TEXT NOT NULL,
	settings TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS clicks (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	campaign_id INTEGER,
	time INTEGER NOT NULL,
	ip TEXT NOT NULL,
	country TEXT,
	lang TEXT,
	os TEXT,
	osver REAL,
	device TEXT,
	brand TEXT,
	model TEXT,
	isp TEXT,
	client TEXT,
	clientver REAL,
	ua TEXT,
	subid TEXT NOT NULL,
	preland TEXT,
	land TEXT,
	params TEXT,
	leaddata TEXT,
	lpclick BOOLEAN,
	status TEXT,
	cost NUMERIC DEFAULT 0,
	payout NUMERIC DEFAULT 0,
	FOREIGN KEY (campaign_id)  REFERENCES campaigns (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_subid ON clicks (subid);
CREATE INDEX IF NOT EXISTS idx_time ON clicks (time);
CREATE INDEX IF NOT EXISTS idx_date ON clicks (date(time, 'unixepoch'));
CREATE INDEX IF NOT EXISTS idx_country ON clicks (country);
CREATE INDEX IF NOT EXISTS idx_lang ON clicks (lang);
CREATE INDEX IF NOT EXISTS idx_lang ON clicks (client);
CREATE INDEX IF NOT EXISTS idx_lang ON clicks (clientver);
CREATE INDEX IF NOT EXISTS idx_lang ON clicks (device);
CREATE INDEX IF NOT EXISTS idx_lang ON clicks (brand);
CREATE INDEX IF NOT EXISTS idx_lang ON clicks (model);
CREATE INDEX IF NOT EXISTS idx_os ON clicks (os);
CREATE INDEX IF NOT EXISTS idx_os ON clicks (osver);
CREATE INDEX IF NOT EXISTS idx_isp ON clicks (isp);


CREATE TABLE IF NOT EXISTS blocked (
	id INTEGER PRIMARY KEY,
	campaign_id INTEGER,
	time INTEGER NOT NULL,
	ip TEXT NOT NULL,
	country TEXT,
	lang TEXT,
	os TEXT,
	osver REAL,
	device TEXT,
	brand TEXT,
	model TEXT,
	isp TEXT,
	client TEXT,
	clientver REAL,
	ua TEXT,
	params TEXT,
	reason TEXT,
	FOREIGN KEY (campaign_id)  REFERENCES campaigns (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_btime ON blocked (time);

CREATE TABLE IF NOT EXISTS global (
    settings TEXT
);
PRAGMA journal_mode = wal;
