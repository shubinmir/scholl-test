'use strict';

/**
 * Клиентская валидация форм регистрации и входа.
 */
(function () {
    var RULES = {
        register: {
            username: function (value) {
                if (!value) return 'Укажите имя пользователя';
                if (!/^[A-Za-z0-9]{3,20}$/.test(value)) return 'Только латиница и цифры, от 3 до 20 символов';
                return '';
            },
            email: function (value) {
                if (!value) return 'Укажите email';
                if (value.indexOf('@') === -1) return 'Email должен содержать @';
                if (!/^[^\s@]+@[^\s@.]+(\.[^\s@.]+)+$/.test(value)) return 'Введите корректный email (например, name@example.com)';
                return '';
            },
            password: function (value) {
                if (!value) return 'Укажите пароль';
                if (value.length < 6) return 'Минимум 6 символов';
                if (!/[A-Za-z]/.test(value) || !/\d/.test(value)) return 'Пароль должен содержать и буквы, и цифры';
                return '';
            },
            password_confirm: function (value, form) {
                if (!value) return 'Повторите пароль';
                if (value !== form.elements.password.value) return 'Пароли не совпадают';
                return '';
            },
            captcha: function (value) {
                if (!value) return 'Ответьте на вопрос капчи';
                if (!/^-?\d+$/.test(value)) return 'Введите число';
                return '';
            }
        },
        login: {
            login: function (value) {
                if (!value) return 'Укажите логин или email';
                return '';
            },
            password: function (value) {
                if (!value) return 'Укажите пароль';
                return '';
            },
            captcha: function (value) {
                if (!value) return 'Ответьте на вопрос капчи';
                if (!/^-?\d+$/.test(value)) return 'Введите число';
                return '';
            }
        }
    };

    function setError(form, name, message) {
        var field = form.querySelector('[data-field="' + name + '"]');
        var slot = form.querySelector('[data-error-for="' + name + '"]');
        if (field) field.classList.toggle('field--invalid', Boolean(message));
        if (slot) slot.textContent = message || '';
    }

    function validateField(form, rules, name) {
        var input = form.elements[name];
        if (!input) return true;
        var message = rules[name](input.value.trim(), form);
        setError(form, name, message);
        return !message;
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var rules = RULES[form.dataset.validate];
        if (!rules) return;

        Object.keys(rules).forEach(function (name) {
            var input = form.elements[name];
            if (!input) return;

            // Первую ошибку показываем при уходе из поля, дальше — по мере правки.
            input.addEventListener('blur', function () {
                validateField(form, rules, name);
            });
            input.addEventListener('input', function () {
                var field = form.querySelector('[data-field="' + name + '"]');
                if (field && field.classList.contains('field--invalid')) {
                    validateField(form, rules, name);
                }
                // Подтверждение пароля зависит от основного поля.
                if (name === 'password' && rules.password_confirm && form.elements.password_confirm.value) {
                    validateField(form, rules, 'password_confirm');
                }
            });
        });

        form.addEventListener('submit', function (event) {
            var firstInvalid = null;

            Object.keys(rules).forEach(function (name) {
                if (!validateField(form, rules, name) && !firstInvalid) {
                    firstInvalid = form.elements[name];
                }
            });

            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.focus();
            }
        });
    });
})();
