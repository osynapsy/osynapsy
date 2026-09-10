window.Osynapsy = window.Osynapsy || {'component' : {}};

Osynapsy.component =
{
    _queue: new Set(),
 
    _isFlushing: false,
    
    _observer: new MutationObserver((mutations) => {
        mutations.forEach(mutation => {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.matches('.osy-component')) {
                            Osynapsy.component.enqueue(node);
                        }
                        node.querySelectorAll('.osy-component').forEach(child => {
                            Osynapsy.component.enqueue(child);
                        });
                    }
                });
            }
        });
    }),

    boot(el)
    {
        if (el.hasAttribute('data-osy-booted')) return;        
        const initPath = el.getAttribute('data-init');
        const initFunc = this.resolveNamespace(initPath, window);
        console.log(initFunc);
        if (!initFunc) {
            console.log('[Osynapsy] La funzione di inizializzazione ${initPath} non esiste.');
            return;                
        }
        try {
            // Esegue la funzione passandogli l'elemento HTML
            initFunc(el);
            el.setAttribute('data-osy-booted', 'true');
        } catch (error) {
            console.error(`[Osynapsy] Errore durante l'esecuzione di ${initPath} su:`, el, error);
        }
    },

    enqueue(element)
    {
        if (!element || element.hasAttribute('data-osy-booted')) return;        
        this._queue.add(element);
        // Schedula lo svuotamento automatico della coda alla fine del microtask corrente.
        // Questo fa sì che se aggiungi 100 elementi di fila, il boot parte una volta sola.
        if (!this._isFlushing) {
            this._isFlushing = true;
            queueMicrotask(() => this.flush());
        }
    },

    flush() {            
        if (this._queue.size === 0) {
            this._isFlushing = false;
            return;
        }

        console.log(`[Osynapsy] Inizio boot di ${this._queue.size} componenti in coda...`);

        // Creiamo una copia della coda attuale e la svuotiamo subito per evitare race condition
        const currentBatch = Array.from(this._queue);
        this._queue.clear();
        this._isFlushing = false;

        // Eseguiamo il boot del batch corrente
        currentBatch.forEach(el => this.boot(el));

        // Lanciamo un evento globale per notificare che il DOM è stato aggiornato e inizializzato
        document.dispatchEvent(new CustomEvent('osynapsy:ready', {
            detail: { bootedElements: currentBatch }
        }));
    },

    // Funzione di utilità per scansionare un intero container (es. al caricamento pagina)
    scan(container = document) {
        container.querySelectorAll('.osy-component:not([data-osy-booted])').forEach(el => {
            this.enqueue(el);
        });
    },

    // Funzione helper per risolvere stringhe con il "dot notation" (es. "BclDatePicker2.initSingle")
    resolveNamespace(path, context = window)
    {
        if (!path) {
            return null;
        }
        const parts = path.split('.');
        let current = context;
        for (const part of parts) {
            if (current === null || current === undefined) {
                return null;
            }
            current = current[part];
        }
        // Restituisce la funzione solo se è effettivamente un costruttore o una funzione eseguibile
        return typeof current === 'function' ? current : null;
    },
    
    reload(components)
    {
        let componentsIDs = Array.isArray(components) ? components : [components];
        let execOnSuccess = arguments.length > 1 ? arguments[1] : null;
        if (componentsIDs.length === 1 && document.getElementById(componentsIDs[0])) {
            Osynapsy.waitMask.show(document.getElementById(componentsIDs[0]));
        }
        let form = document.querySelector('form');
        let response = fetch(window.location.href, {
            body: new FormData(form),
            method: 'post',
            headers: {
                'Osynapsy-Html-Components': componentsIDs.join(';'),
                'Accept': 'text/html'
            }
        });
        response.then(response => response.text())
                .then(strHtmlPage => {
            Osynapsy.waitMask.remove();
            let parser = new DOMParser();
            let remoteDoc = parser.parseFromString(strHtmlPage, 'text/html');            
            componentsIDs.forEach(componentId => {                
                let remoteComponent = remoteDoc.getElementById(componentId);
                let localComponent = document.getElementById(componentId);
                if (remoteComponent && localComponent) {               
                    localComponent.replaceWith(remoteComponent);
                    document.getElementById(componentId).dispatchEvent(new CustomEvent('afterRefresh', {detail: { componentId }, bubbles: true}));
                }
            });
            if (remoteDoc.getElementById('responseLibs')) {
                let appended = 0;
                Array.from(remoteDoc.getElementById('responseLibs').children).forEach(elm => {                    
                    if (document.getElementById(elm.getAttribute('id'))) {                        
                        return;
                    }                   
                    document.body.append(!elm.hasAttribute('src') ? elm : Osynapsy.createElement('script', { 
                        'id' : elm.getAttribute('id'), 
                        'src' : elm.getAttribute('src')
                    }));
                    appended++;
                });
                if (appended) {
                    setTimeout(() => {Osynapsy.plugin.init(); }, 500);
                }                
            }            
            setTimeout(() => {Osynapsy.component.flush(); }, 700);
            if (typeof execOnSuccess === 'function') {                
                execOnSuccess();
            }
        }).catch(error => {
            Osynapsy.waitMask.remove();
            console.log(error);
        });
    }
};
