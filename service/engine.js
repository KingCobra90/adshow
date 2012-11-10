var mysql = require('mysql');
var Placements = require('./placements.js');
var Units = require('./units.js');

var Engine = function(settings) {
    this.units = new Units();
    this.placements = new Placements();

    var dbSettings = settings['Database settings'];
    var serviceSettings = settings['Service Settings'];

    this.config = {
        host : dbSettings.dbhost,
        user : dbSettings.dbuser,
        password : dbSettings.dbpass,
        database: dbSettings.dbname,
        multipleStatements: true
    };
    this.connected = false;

    this.reconnect();
    setInterval(this.load.bind(this), serviceSettings.dbrefresh);
};

module.exports = Engine;

Engine.prototype.reconnect = function() {
    console.log('Something broke. Reconnect.');
    this.connected = true;
    this.connection = mysql.createClient(this.config);
	this.connected = true;
	this.load();
	/*this.connection.connected always false, why?
	if(this.connection.connected==false) {
	    setTimeout(this.reconnect.bind(this), 10000);
        return;
	}
	else {
		this.connected = true;
		console.log('Connected.');
		this.load();
	}*/

};

Engine.prototype.load = function() {
    if(!this.connected) return;

    this.connection.query('START TRANSACTION;', function(err, result) {
        if (err && err.fatal) {
            this.reconnect();
            return;
        }
    }.bind(this));	
	
	this.units.deleteOverLimit(this);

    this.connection.query('SELECT * FROM unit WHERE status="active" AND ((views_limit>0 AND views_limit>shows) OR views_limit=0) AND ((clicks_limit>0 AND clicks_limit>clicks) OR clicks_limit=0);', function(err, result) {
        if (err && err.fatal) {
            this.reconnect();
            return;
        }
		this.units.load(result);
    }.bind(this));	
	
    this.connection.query('SELECT * FROM bindings;', function(err, result) {
        if (err && err.fatal) {
            this.reconnect();
            return;
        }
		this.placements.load(result, this.units);
    }.bind(this));	
	
    this.connection.query('COMMIT;', function(err, result) {
        if (err && err.fatal) {
            this.reconnect();
            return;
        }
    }.bind(this));	
	
};

Engine.prototype.getCodeAndName = function(placementId, imageServer) {
    var unit = this.placements.getUnit(placementId);
    if(unit) {
        return {'code': unit.getCode(imageServer).replace('{url}', '/click/' + unit.name + '?rand='+new Date().getTime()), 'name': unit.name};
    }
};

Engine.prototype.getLinkAndName = function(unitId) {
    var unit = this.units.getUnit(unitId);
    if(unit) {
        return {'link': unit.link, 'name': unit.name};
    }
};

Engine.prototype.getDefaultImage = function(res, imageServer) {
	var defaultImage='<img src="'+imageServer+'/img/defaultimage.jpg" alt="sc2tv.ru"/>';
	return defaultImage;
};
