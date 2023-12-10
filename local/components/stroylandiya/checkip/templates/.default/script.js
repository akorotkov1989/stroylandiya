BX.ready(function(){
    /**
     * Обработка отправки формы для получение информации по IP адресу
     */
    BX.bind(BX('ipchecker'), 'submit', BX.delegate(function(event) {
        event.preventDefault();

        let ipAddress = BX('ip-address').value;
        if (!ValidateIpAddress(ipAddress)) {
            BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + BX.message('STROYLANDIYA_NOT_VALID_IP') + '</span>');
        } else {
            let formData = new FormData();
            formData.append('ipAddress', ipAddress);

            BX.ajax.runComponentAction('stroylandiya:checkip', 'getIpInfo',{
                mode: 'class',
                data: formData,
            }).then(function (response) {
                const data = JSON.parse(response.data.message);
                // Вывод ответа в HTML
                renderJSON(data, document.querySelector('.b-ipinfo'));
            }).catch(function(response) {
                if (response.errors[0].message !== '') {
                    BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + response.errors[0].message + '</span>');
                } else {
                    BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + BX.message('STROYLANDIYA_UNDEFINED_ERROR_MESSAGE') + '</span>');
                }
            });
        }
    }, this));

    /**
     * Валидация IP адреса
     * @param ipaddress
     * @returns {boolean}
     * @constructor
     */
    function ValidateIpAddress(ipaddress) {
        let isValidIp = false;
        if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) {
            isValidIp = true;
        }

        return isValidIp;
    }

    /**
     * Вывод Json в HTML
     * @param json
     * @param container
     */
    function renderJSON(json, container) {
        // Очистить контейнер перед отображением нового JSON
        container.innerHTML = '';

        // Рекурсивная функция для обхода JSON
        function renderObject(obj, parentElement) {
            // Создать div для текущего уровня объекта
            const div = document.createElement('div');
            div.classList.add('level');

            // Обойти все свойства объекта
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    const value = obj[key];

                    // Создать div для свойства и добавить его в текущий уровень
                    const propertyDiv = document.createElement('div');
                    propertyDiv.classList.add('property');
                    const propertySpan = document.createElement('span');
                    propertySpan.classList.add('property-span-key');
                    propertySpan.textContent = `${key}:`;
                    propertyDiv.appendChild(propertySpan);

                    // Если значение свойства является объектом, вызвать рекурсивно функцию renderObject для отображения вложенных свойств
                    if (typeof value === 'object' && value !== null) {
                        renderObject(value, propertyDiv);
                    } else {
                        // Если значение свойства не является объектом, просто отобразить его
                        const valueSpan = document.createElement('span');
                        valueSpan.classList.add('property-span-value');
                        valueSpan.textContent = value;
                        propertyDiv.appendChild(valueSpan);
                    }

                    // Добавить свойство в текущий уровень
                    div.appendChild(propertyDiv);
                }
            }

            // Добавить текущий уровень в родительский элемент
            parentElement.appendChild(div);
        }

        // Начать отображение JSON с корневого объекта
        renderObject(json, container);
    }
});