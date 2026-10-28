// Chat para block_helizllama. Etapa placeholder: respuestas fijas por rol,
// todavía sin backend de IA/RAG (ver local_helizllama/classes/local/faq_service.php).
//
// Deliberadamente NO usa core/modal: un modal pone un backdrop que bloquea la
// página, y el asistente debe quedar abierto mientras se navega el curso.
define(['core/str'], function(Str) {

    var PANEL_ID = 'helizllama_panel';
    var FAB_ID = 'helizllama_fab';

    var appendMessage = function(panel, who, text) {
        var log = panel.querySelector('#helizllama_chat_log');
        var line = document.createElement('div');
        line.className = 'mb-2';
        var strong = document.createElement('strong');
        strong.textContent = who + ': ';
        line.appendChild(strong);
        line.appendChild(document.createTextNode(text));
        log.appendChild(line);
        log.scrollTop = log.scrollHeight;
    };

    var ask = function(cfg, question) {
        var formdata = new FormData();
        formdata.append('sesskey', cfg.sesskey);
        formdata.append('courseid', cfg.courseid);
        formdata.append('question', question);

        return fetch(M.cfg.wwwroot + '/blocks/helizllama/ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formdata
        }).then(function(response) {
            // El servidor devuelve {error: "..."} con 429 (limite de peticiones)
            // o 400 (pregunta demasiado larga); ese texto si se le muestra al
            // usuario porque es accionable.
            return response.json().catch(function() {
                throw new Error('Respuesta no valida');
            });
        }).then(function(data) {
            if (data.error) {
                return data.error;
            }
            return data.answer;
        });
    };

    var togglePanel = function(panel, open) {
        var fab = document.getElementById(FAB_ID);
        panel.style.display = open ? 'flex' : 'none';
        if (fab) {
            fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (open) {
            var input = panel.querySelector('#helizllama_chat_input');
            if (input) {
                input.focus();
            }
        } else if (fab) {
            fab.focus();
        }
    };

    var buildPanel = function(cfg, strs) {
        var panel = document.createElement('div');
        panel.id = PANEL_ID;
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', strs.modaltitle);
        panel.className = 'bg-white border rounded shadow-lg';
        panel.style.cssText = [
            'position:fixed',
            'bottom:88px',
            'right:20px',
            'width:360px',
            'max-width:calc(100vw - 40px)',
            'max-height:min(70vh, 560px)',
            'display:none',
            'flex-direction:column',
            'z-index:1035'
        ].join(';');

        panel.innerHTML =
            '<div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">' +
                '<div>' +
                    '<div class="fw-bold">' + strs.modaltitle + '</div>' +
                    '<div class="small text-muted">' + strs.roleindicator + '</div>' +
                '</div>' +
                '<button type="button" id="helizllama_close" class="btn-close" ' +
                    'aria-label="' + strs.close + '"></button>' +
            '</div>' +
            '<div id="helizllama_chat_log" class="px-3 py-2" ' +
                'style="flex:1 1 auto;overflow-y:auto;min-height:180px;"></div>' +
            '<div class="input-group p-2 border-top">' +
                '<input type="text" id="helizllama_chat_input" class="form-control" ' +
                    'placeholder="' + strs.placeholder + '">' +
                '<button type="button" id="helizllama_chat_send" class="btn btn-primary">' +
                    strs.send + '</button>' +
            '</div>';

        document.body.appendChild(panel);

        var sendCurrent = function() {
            var input = panel.querySelector('#helizllama_chat_input');
            var question = input.value.trim();
            if (!question) {
                return;
            }
            appendMessage(panel, 'Tú', question);
            input.value = '';
            ask(cfg, question).then(function(answer) {
                appendMessage(panel, 'HelizLlama', answer);
                return null;
            }).catch(function() {
                appendMessage(panel, 'HelizLlama', strs.errorgeneric);
            });
        };

        panel.querySelector('#helizllama_chat_send').addEventListener('click', sendCurrent);
        panel.querySelector('#helizllama_chat_input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendCurrent();
            }
        });
        panel.querySelector('#helizllama_close').addEventListener('click', function() {
            togglePanel(panel, false);
        });

        return panel;
    };

    var buildFab = function(strs) {
        var fab = document.createElement('button');
        fab.type = 'button';
        fab.id = FAB_ID;
        fab.className = 'btn btn-primary rounded-circle shadow';
        fab.setAttribute('aria-label', strs.opentitle);
        fab.setAttribute('title', strs.opentitle);
        fab.setAttribute('aria-expanded', 'false');
        fab.setAttribute('aria-controls', PANEL_ID);
        fab.style.cssText = [
            'position:fixed',
            'bottom:20px',
            // A la izquierda de la burbuja "?" de Moodle para no taparla.
            'right:84px',
            'width:56px',
            'height:56px',
            'padding:0',
            'z-index:1035',
            'display:flex',
            'align-items:center',
            'justify-content:center',
            'font-size:1.4rem',
            'line-height:1'
        ].join(';');
        fab.innerHTML = '<i class="fa fa-comments" aria-hidden="true"></i>';
        document.body.appendChild(fab);
        return fab;
    };

    var init = function(cfg) {
        if (document.getElementById(FAB_ID)) {
            return;
        }

        var roleKey = cfg.isteacher ? 'roleindicator_teacher' : 'roleindicator_student';
        Str.get_strings([
            {key: 'modaltitle', component: 'block_helizllama'},
            {key: 'placeholder', component: 'block_helizllama'},
            {key: 'send', component: 'block_helizllama'},
            {key: 'errorgeneric', component: 'block_helizllama'},
            {key: 'opentitle', component: 'block_helizllama'},
            {key: roleKey, component: 'block_helizllama'},
            {key: 'closebuttontitle', component: 'moodle'}
        ]).then(function(s) {
            var strs = {
                modaltitle: s[0],
                placeholder: s[1],
                send: s[2],
                errorgeneric: s[3],
                opentitle: s[4],
                roleindicator: s[5],
                close: s[6]
            };

            var fab = buildFab(strs);
            var panel = buildPanel(cfg, strs);

            fab.addEventListener('click', function() {
                togglePanel(panel, panel.style.display === 'none');
            });

            var blockbtn = document.getElementById('block_helizllama_open_btn');
            if (blockbtn) {
                blockbtn.addEventListener('click', function() {
                    togglePanel(panel, true);
                });
            }
            return null;
        }).catch(function() {
            return null;
        });
    };

    return {
        init: init
    };
});
