var Y = YAHOO;
var U = Y.util;
var D = U.Dom;
var E = U.Event;
var C = U.Connect;
var T = Y.namespace("tweeem");

T.LOGIN_ID = 'loginPanel';
T.CHAT_ID = 'chatPanel';
T.LIST_ID = 'listPanel';

T.MESSAGE_LENGTH_LIMIT = 140;

/**
 * Объект для отображения либо скрывания сообщения о загрузке
 */
T.Loading = new function()
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Счётчик количества запросов ожидающих загрузку в данный помент
	 *
	 * @type int
	 * @access private
	 */
	var counter = 0;

	/**********
	 * Методы *
	 **********/

	/**
	 * Показать сообщение о заргузке
	 *
	 * @access public
	 */
	this.show = function()
	{
		if( counter++ == 0 )
		{	D.setStyle(D.get('loading'), 'visibility', 'visible');
		}
	};

	/**
	 * Убрать сообщение о загрузке
	 *
	 * @access public
	 */
	this.hide = function()
	{
		if( --counter <= 0 )
		{	D.setStyle(D.get('loading'), 'visibility', 'hidden');
		}
	};
}

/**
 * Объект для отображения сообщений
 */
T.Message = new function()
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Время отображения ошибки в секундах
	 */
	var LIFETIME = 60;

	/************
	 * Свойства *
	 ************/

	/**********
	 * Методы *
	 **********/

	/**
	 * Показать сообщение о заргузке
	 *
	 * @access public
	 */
	this.show = function(message)
	{
		D.get('message').firstChild.firstChild.nodeValue = message;
		setTimeout(function(){D.get('message').firstChild.firstChild.nodeValue = '';}, LIFETIME*1000);
	};

	/**
	 * Убрать сообщение о загрузке
	 *
	 * @access public
	 */
	this.hide = function()
	{
		D.get('message').firstChild.firstChild.nodeValue = '';
	};
}

/**
 * Объект для работы с панелями
 */
T.Panels = new function()
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Константы для события разворачивания панели
	 *
	 * Позаимствовано из YUI примеров
	 */
	var IE_QUIRKS = (YAHOO.env.ua.ie && document.compatMode == "BackCompat");
	var IE_SYNC = (YAHOO.env.ua.ie == 6 || (YAHOO.env.ua.ie == 7 && IE_QUIRKS));

	/************
	 * Свойства *
	 ************/

	/**
	 * Менеджер панелей
	 *
	 * @type object
	 * @access private
	 */
	var manager = new Y.widget.OverlayManager();

	/**
	 * Обработчики событий
	 *
	 * @type object
	 * @access private
	 */
	var handlers =
	{
		/**
		 * Обработчик события сворачивания панели
		 *
		 * this указывает на панель вызвавшую событие
		 *
		 * @param e Аргументы события
		 */
		minimize: function(e)
		{
			/**
			 * Создание нового элемента для добавления в контейнер
			 */
			var minimized = document.createElement('div');
			D.addClass(minimized, 'minimized');
			E.addListener(minimized, 'dblclick', handlers.maximize, {panel: this, minimized: minimized}, true);

			/**
			 * Копирование заголовка
			 */
			minimized.appendChild(this.header.firstChild.cloneNode(true));

			/**
			 * Создание кнопки и события для разворачивания
			 */
			var id = 'maximize_' + this.header.firstChild.firstChild.nodeValue;
			var maximize = document.createElement('div');
			maximize.setAttribute('id', id);
			D.addClass(maximize, 'maximize');
			E.addListener(maximize, 'click', handlers.maximize, {panel: this, minimized: minimized}, true);
			minimized.appendChild(maximize);

			/**
			 * Добавление элемента в контейнер
			 */
			D.get('minimizedContainer').appendChild(minimized);

			this.hide();
			this.blur();
		},

		/**
		 * Обработчик события разворачивания панели
		 *
		 * this указывает на объект с панелью и минимизированным дивом
		 *
		 * @param e Аргументы события
		 */
		maximize: function(e)
		{
			D.get('minimizedContainer').removeChild(this.minimized);
			this.panel.focus();
			this.panel.show();
		},

		/**
		 * Обработчик события изменения размера панели
		 * Фиксит размеры содержимого
		 *
		 * this указывает на объект панели вызывающей событие
		 *
		 * @param args Аргументы события
		 */
		resize: function(args)
		{
			this.focus();

			fixBody(this, args.height);

			if(IE_SYNC)
			{	this.sizeUnderlay();
				this.syncIframe();
			}
		},

		/**
		 * Обработчик события изменения размера панели
		 * Устанавливает позицию скроллбара вниз
		 *
		 * this указывает на объект панели вызывающей событие
		 *
		 * @param args Аргументы события
		 */
		scrollTimelineToBottom: function(args)
		{
			timeline = D.getElementsByClassName('timeline', null, this.body);
			if( timeline != undefined && timeline[0] != undefined )
			{
				timeline[0].scrollTop = timeline[0].scrollHeight;
			}
		}
	}

	/**********
	 * Методы *
	 **********/

	/**
	 * Метод для правки внутренних размеров панели при ресайзе
	 *
	 * Позаимствовано из YUI примеров
	 *
	 * @access private
	 * @param object p Панель для правки
	 * @param int height Высота панели. Передаётся при ресайзе (optional)
	 */
	var fixBody = function(p, height)
	{
		height = ( height == undefined ? p.element.offsetHeight - 2 /* Толщина границы снизу панели. Проблемы с динамическим получением этого значения */: height );

		// Content + Padding + Border
		var headerHeight = p.header.offsetHeight;
		var footerHeight = p.footer.offsetHeight;

		var bodyHeight = (height - headerHeight - footerHeight);
		var bodyContentHeight = (IE_QUIRKS) ? bodyHeight : bodyHeight - parseInt(D.getStyle(p.body, 'padding-top')) - parseInt(D.getStyle(p.body, 'padding-bottom'));

		D.setStyle(p.body, 'height', bodyContentHeight + 'px');
	};

	/**
	 * Создание панели
	 *
	 * @access private
	 * @param args Параметры
	 * @return object Ссылка на созданную панель
	 */
	var createPanel = function(args)
	{
		if( args.id == undefined )
		{	return false;
		}

		/**
		 * Создание панели
		 */
		var panel = new Y.widget.Panel(args.id, {
			width: args.width +'px',
			height: args.height +'px',
			xy: args.xy,
			close: ( args.close != undefined ? args.close : false ),
			visible: true,
			constraintoviewport: true,
			draggable: args.draggable,
			underlay: "shadow",
			effect:[{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.25}]
		} );

		/**
		 * Если создаётся панель с прямыми сообщениями
		 */
		if( args.direct != undefined )
		{
			D.addClass(panel.element.firstChild, 'direct');
			D.addClass(panel.element.firstChild, 'panel');
			panel.setHeader('<span>'+ args.direct.title +'</span><div class="container-minimize aside"></div>');
			panel.setBody('<div id="direct_'+ args.direct.name +'_timeline" class="timeline"></div><div id="direct_'+ args.direct.name +'_update" class="update"><form action="/ajax/directUpdate/'+ args.direct.name +'/" name="direct_'+ args.direct.name +'_form"><div class="text"><div class="area"><textarea id="direct_'+ args.direct.name +'_textarea" name="direct[text]"></textarea></div><div class="counter"><span id="direct_'+ args.direct.name +'_counter">'+ T.MESSAGE_LENGTH_LIMIT +'</span></div><div class="button" id="direct_'+ args.direct.name +'_updateBtn"/>UPDATE</div></div></form></div>');
			panel.setFooter('<span></span>');
		}

		/**
		 * Отрисовка и правка содержимого в соответствии с размерами
		 */
		panel.render(D.get('doc'));
		fixBody(panel);

		/**
		 * Регистрация панели в менеджере
		 */
		manager.register(panel);

		/**
		 * Если кнопочка существует вешаем событие сворачивания панели
		 */
		var minimizeBtn = D.getElementsByClassName('container-minimize', null, panel.header);
		if( minimizeBtn.length > 0 )
		{	E.addListener(minimizeBtn[0], 'click', handlers.minimize, panel, true);
			E.addListener(panel.header, 'dblclick', handlers.minimize, panel, true);
		}

		/**
		 * Если необходимо делаем панель изменяемой в размерах
		 */
		if( args.resizable == undefined || args.resizable == true )
		{
			var resize = new U.Resize(args.id, {
				handles: ['br'],
				autoRatio: false,
				minWidth: ( args.minWidth != undefined ? args.minWidth : 192 ),
				minHeight: ( args.minHeight != undefined ? args.minHeight : 128 ),
				status: false
			});

			/**
			 * Обработчик события ресайза для правки внутренностей панели
			 */
			resize.on('resize', handlers.resize, panel, true);

			/**
			 * На панель чата, либо прямых сообщений вешаем обработчик события рейсайза для установки скрола вниз
			 */
			if( args.id == T.CHAT_ID )
			{
				resize.on('resize', handlers.scrollTimelineToBottom, panel, true);
			}
		}

		return panel;
	};

	/**
	 * Создать панель входа в систему
	 *
	 * @access public
	 * @return object Ссылка на созданную панель
	 */
	this.createLogin = function()
	{
		var id = T.LOGIN_ID;

		if( D.get(id) == undefined )
		{
			return null;
		}

		var offset = Y.widget.Overlay.VIEWPORT_OFFSET;
		var hdHeight = parseInt(D.getStyle('hd', 'height'));
		var x = offset;
		var y = hdHeight+offset;

		var panel = createPanel({
			id: id,
			xy: [x, y],
			resizable: false
		});

		return panel;
	};

	/**
	 * Создать панель общего чата
	 *
	 * @access public
	 * @return object Ссылка на созданную панель
	 */
	this.createChat = function()
	{
		var id = T.CHAT_ID;
		var offset = Y.widget.Overlay.VIEWPORT_OFFSET;
		var hdHeight = parseInt(D.getStyle('hd', 'height'));
		var ftHeight = parseInt(D.getStyle('ft', 'height'));
		var width = Math.round((D.getViewportWidth() - offset*2) - 250 - 10 - 10);
		var height = D.getViewportHeight() - offset*2 - hdHeight - ftHeight - 16;
		var x = offset;
		var y = hdHeight + offset;

		var panel = createPanel({
			id: id,
			xy: [x, y],
			width: width,
			height: height,
			minWidth: 320,
			minHeight: 250
		});

		return panel;
	};

	/**
	 * Создать панель списка контактов
	 *
	 * @access public
	 * @return object Ссылка на созданную панель
	 */
	this.createList = function()
	{
		var id = T.LIST_ID;

		if( D.get(id) == undefined )
		{
			return null;
		}

		var offset = Y.widget.Overlay.VIEWPORT_OFFSET;
		var hdHeight = parseInt(D.getStyle('hd', 'height'));
		var ftHeight = parseInt(D.getStyle('ft', 'height'));
		var width = 250;
		var height = D.getViewportHeight() - offset*2 - hdHeight - ftHeight - 16;
		var x = D.getViewportWidth()-width-offset;
		var y = hdHeight+offset;

		var panel = createPanel({
			id: id,
			xy: [x, y],
			width: width,
			height: height,
			minWidth: 250,
			minHeight: 250
		});

		return panel;
	};

	/**
	 * Динамически создать и отобразить панель прямого сообщения пользователю
	 *
	 * @access public
	 * @return object Ссылка на созданную панель
	 */
	this.createDirect = function(name)
	{
		var id = 'direct_'+name;
		var viewportW = D.getViewportWidth();
		var viewportH = D.getViewportHeight();
		var width = Math.round(viewportW*0.5);
		var height = Math.round(viewportH*0.5);
		var x = Math.round((viewportW-width)/2);
		var y = Math.round((viewportH-height)/2);

		var panel = createPanel({
			id: id,
			xy: [x, y],
			width: width,
			height: height,
			close: true,
			direct: {
				name: name,
				title: '@'+name
			}
		});

		return panel;
	};
}

T.Ajax = new function()
{
	/************
	 * Свойства *
	 ************/

	/**
	 * Обработчики событий начала запроса и конца
	 */
	var handlers =
	{
		start: function(eventType, args)
		{
			T.Loading.show();
		},

		complete: function(eventType, args)
		{
			T.Loading.hide();
		}
	};

	/**********
	 * Методы *
	 **********/

	/**
	 * Конструктор
	 */
	(function(){
		C.startEvent.subscribe(handlers.start, handlers);
		C.completeEvent.subscribe(handlers.complete, handlers);
	})();

	/**
	 * Добавить сообщение к таймлайну
	 *
	 * @access private
	 */
	var addTimelineItem = function(timeline, message)
	{
		if( timeline != undefined )
		{
			var temp = D.getElementsByClassName('last', null, timeline);
			for(var i=0; i<temp.length; i++)
			{	D.removeClass(temp[i], 'last');
			}

			var item = document.createElement('div');
			var img = document.createElement('img');
			var text = document.createElement('div');
			var name = document.createElement('span');
			var msg = document.createElement('span');

			D.addClass(item, 'item');
			D.addClass(item, 'last');
			D.addClass(text, 'text');
			D.addClass(name, 'name');
			D.addClass(msg, 'msg');

			name.appendChild(document.createTextNode(T.User.login));

			msg.appendChild(document.createTextNode(message));

			text.appendChild(name);
			text.appendChild(document.createTextNode(' '));
			text.appendChild(msg);

			img.setAttribute('alt', T.User.login);
			img.setAttribute('src', T.User.profile_image.mini);

			item.appendChild(img);
			item.appendChild(text);

			timeline.appendChild(item);

			timeline.scrollTop = timeline.scrollHeight;
		}
	};

	/**
	 * Получить и установить таймлайн чата
	 *
	 * @access public
	 * @param object panel Панель чата
	 */
	this.handleChatTimeline = function(panel)
	{
		var callback =
		{
			/**
			 * Добавляем тайм-лайн
			 */
			success: function(o)
			{
				var timeline = D.get('chatTimeline');
				timeline.innerHTML = o.responseText;
				timeline.scrollTop = timeline.scrollHeight;
			},

			failure: function(o)
			{
				T.Message.show('Operation could not be completed due to internal server error');
			},

			cache: false
		}

		var request = C.asyncRequest('GET', '/ajax/chatTimeline/', callback);
	};

	/**
	 * Отправить сообщение
	 *
	 * @access public
	 * @param object panel Панель чата
	 */
	this.handleChatUpdate = function(panel)
	{
		var textarea = D.get('chatTextarea');
		var message = textarea.value;

		var callback =
		{
			success: function(o)
			{
				textarea.value = '';
				var counter = D.get('chatUpdateCounter');
				counter.firstChild.nodeValue = T.MESSAGE_LENGTH_LIMIT;
				D.setStyle(counter, 'color', 'navy');
				T.Message.show('Message received');

				addTimelineItem(D.get('chatTimeline'), message);
			},

			failure: function(o)
			{
				T.Message.show('Operation could not be completed due to internal server error');
			},

			cache: false
		}

		if( message != '' && message.length <= T.MESSAGE_LENGTH_LIMIT )
		{	C.setForm('chat');
			var request = C.asyncRequest('POST', '/ajax/chatUpdate/', callback);
		}
	};

	/**
	 * Получить и установить список контактов
	 *
	 * @access public
	 * @param object panel Панель списка контактов
	 */
	this.handleContactList = function(panel)
	{
		var callback =
		{
			/**
			 * Добавляем тайм-лайн
			 */
			success: function(o)
			{
				var list = D.get('list');
				list.innerHTML = o.responseText;

				var items = D.getElementsByClassName('item', null, list);
				for(var i=0; i<items.length; i++)
				{
					var screenName = D.getElementsByClassName('screenName', null, items[i]);
					screenName = screenName[0].firstChild.nodeValue;
					E.on(items[i], 'click', T.Init.direct, screenName);
					E.on(items[i], 'mouseover', function(e){D.addClass(this, 'hover')}, items[i], true);
					E.on(items[i], 'mouseout', function(e){D.removeClass(this, 'hover')}, items[i], true);
				}
			},

			failure: function(o)
			{
				T.Message.show('Operation could not be completed due to internal server error');
			},

			cache: false
		}

		var request = C.asyncRequest('GET', '/ajax/contactList/', callback);
	};

	/**
	 * Получить и установить таймлайн окна прямого общения
	 *
	 * @access public
	 * @param object panel Панель прямого общения
	 */
	this.handleDirectTimeline = function(panel, name)
	{
		var callback =
		{
			/**
			 * Добавляем тайм-лайн
			 */
			success: function(o)
			{
				var timeline = D.get('direct_'+ name +'_timeline');
				timeline.innerHTML = o.responseText;
				timeline.scrollTop = timeline.scrollHeight;
			},

			failure: function(o)
			{
				T.Message.show('Operation could not be completed due to internal server error');
			},

			cache: false
		}

		var request = C.asyncRequest('GET', '/ajax/directTimeline/'+ name +'/', callback);
	};

	/**
	 * Отправить прямое сообщение
	 *
	 * @access public
	 * @param object panel Панель прямого общения
	 */
	this.handleDirectUpdate = function(panel, name)
	{
		var textarea = D.get('direct_'+ name +'_textarea');
		var message = textarea.value;

		var callback =
		{
			success: function(o)
			{
				textarea.value = '';
				var counter = D.get('direct_'+ name +'_counter');
				counter.firstChild.nodeValue = T.MESSAGE_LENGTH_LIMIT;
				D.setStyle(counter, 'color', 'navy');
				T.Message.show('Message received');

				addTimelineItem(D.get('direct_'+ name +'_timeline'), message);
			},

			failure: function(o)
			{
				T.Message.show('Operation could not be completed due to internal server error');
			},

			cache: false
		}

		if( message != '' && message.length <= T.MESSAGE_LENGTH_LIMIT )
		{	C.setForm('direct_'+ name +'_form');
			var request = C.asyncRequest('POST', '/ajax/directUpdate/'+ name +'/', callback);
		}
	};
}

T.Init = new function()
{
	/*************
	 * Константы *
	 *************/

	/**
	 * Интервал обновления окна чата
	 * В секундах
	 */
	var CHAT_TIMELINE_INTERVAL = 360;//62*1000;

	/**
	 * Интервал обновления окна чата
	 * В секундах
	 */
	var LIST_INTERVAL = 360;//62*1000;

	/**
	 * Интервал обновления окна прямого общения
	 * В секундах
	 */
	var DIRECT_TIMELINE_INTERVAL = 360;//62*1000;

	/**
	 * Цвета, между которыми усуществлять переход цвета числа на счётчике в процессе изменения его значения
	 */
	var COUNTER_FROM_COLOR = {R: 0x00, G: 0x00, B: 0x80};
	var COUNTER_TO_COLOR = {R: 0xFF, G: 0x00, B: 0x00};

	/************
	 * Свойства *
	 ************/

	/**
	 * Обработчики событий
	 *
	 * @type object
	 * @access private
	 */
	var handlers =
	{
		/**
		 * Обработчик события ввода текста
		 *
		 * this указывает на объект содержащий два поля: textarea порождающий событие и counter счётчик который необходимо изменить
		 *
		 * @param e Аргументы события
		 */
		counter: function(e)
		{
			/**
			 * Текст
			 */
			var text = this.textarea.value;

			/**
			 * Сколько символов осталось ввести
			 */
			var remainder = T.MESSAGE_LENGTH_LIMIT - text.length;
			this.counter.firstChild.nodeValue = remainder;

			if( remainder < 0 )
			{	if( !D.hasClass(this.button, 'disabled') )
				{	D.addClass(this.button, 'disabled');
				}
			}
			else if( D.hasClass(this.button, 'disabled') )
			{	D.removeClass(this.button, 'disabled');
			}

			/**
			 * Округляем до положительных чисел остаток для использования в расчёте градиента
			 */
			remainder = ( remainder < 0 ? 0 : remainder );

			/**
			 * Получаем цвета для перехода
			 */
			var from = COUNTER_FROM_COLOR;
			var to = COUNTER_TO_COLOR;

			/**
			 * Вычисляем текущий цвет
			 */
			var temp = (text.length/T.MESSAGE_LENGTH_LIMIT);
			temp = ( temp > 1 ? 1 : temp );
			var color =
			{	R: from.R + Math.round((to.R-from.R)*temp),
				G: from.G + Math.round((to.G-from.G)*temp),
				B: from.B + Math.round((to.B-from.B)*temp)
			}

			/**
			 * Устанавливаем цвет счётчику
			 */
			D.setStyle(this.counter, 'color', 'rgb('+ color.R +', '+ color.G +', '+ color.B +')');
		}
	}

	/**
	 * Свойства для хранения ссылок на используемые панели
	 *
	 * @access public
	 */
	this.loginPanel = null;
	this.chatPanel = null;
	this.listPanel = null;

	/**
	 * Свойство для хранения массива панелей с прямыми сообщениями
	 *
	 * @access public
	 */
	this.directPanels = new Array();

	/**********
	 * Методы *
	 **********/

	/**
	 * Инициализировать окно для входа в систему
	 *
	 * @access public
	 */
	this.login = function()
	{
		if( D.get(T.LOGIN_ID) == undefined )
		{
			return null;
		}

		var p;
		p = this.loginPanel = T.Panels.createLogin();
	}

	/**
	 * Инициализировать окно чата
	 *
	 * @access public
	 */
	this.chat = function()
	{
		if( D.get(T.CHAT_ID) == undefined )
		{
			return null;
		}

		var p = this.chatPanel = T.Panels.createChat();

		var id = 'chatUpdateBtn';
		E.on(id, 'click', function(e){T.Ajax.handleChatUpdate(p)});
		E.on(id, 'mouseover', function(e){D.addClass(this, 'hover')}, D.get(id), true);
		E.on(id, 'mouseout', function(e){D.removeClass(this, 'hover')}, D.get(id), true);
		var temp = {textarea: D.get('chatTextarea'), counter: D.get('chatUpdateCounter'), button: D.get(id)};
		E.on('chatTextarea', 'keyup', handlers.counter, temp, true);
		E.on('chatTextarea', 'keypress', handlers.counter, temp, true);

		T.Ajax.handleChatTimeline(p);
		setInterval(function(){T.Ajax.handleChatTimeline(p)}, CHAT_TIMELINE_INTERVAL*1000);
	}

	/**
	 * Инициализировать окно списка контактов
	 *
	 * @access public
	 */
	this.list = function()
	{
		if( D.get(T.LIST_ID) == undefined )
		{
			return null;
		}

		var p = this.listPanel = T.Panels.createList();

		T.Ajax.handleContactList(p);
		setInterval(function(){T.Ajax.handleContactList(p)}, LIST_INTERVAL*1000);
	}

	/**
	 * Открыть окно для отправки прямых сообщений
	 *
	 * @access public
	 */
	this.direct = function(e, name)
	{
		if( T.Init.directPanels[name] == undefined )
		{
			var panel = T.Init.directPanels[name] = T.Panels.createDirect(name);

			var id = 'direct_'+ name +'_updateBtn';
			E.on(id, 'click', function(e){T.Ajax.handleDirectUpdate(panel, name)});
			E.on(id, 'mouseover', function(e){D.addClass(this, 'hover')}, D.get(id), true);
			E.on(id, 'mouseout', function(e){D.removeClass(this, 'hover')}, D.get(id), true);
			var textareaId = 'direct_'+ name +'_textarea';
			var temp = {textarea: D.get(textareaId), counter: D.get('direct_'+ name +'_counter'), button: D.get(id)};
			E.on(textareaId, 'keyup', handlers.counter, temp, true);
			E.on(textareaId, 'keypress', handlers.counter, temp, true);

			T.Ajax.handleDirectTimeline(panel, name);
			setInterval(function(){T.Ajax.handleDirectTimeline(panel, name)}, DIRECT_TIMELINE_INTERVAL*1000);
		}
		else
		{
			var panel = T.Init.directPanels[name];
			var maximize = D.get('maximize_' + panel.header.firstChild.firstChild.nodeValue);

			if( maximize != undefined )
			{
				var evObj = document.createEvent('MouseEvents');
				evObj.initMouseEvent( 'click', true, true, window, 1, 12, 345, 7, 220, false, false, true, false, 0, null );
				maximize.dispatchEvent(evObj);
			}
			else
			{
				panel.focus();
				panel.show();
			}
		}
	}
}



E.onContentReady(T.LOGIN_ID, T.Init.login);
E.onContentReady(T.CHAT_ID, T.Init.chat);
E.onContentReady(T.LIST_ID, T.Init.list);