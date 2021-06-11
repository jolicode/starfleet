const TomSelect = require('tom-select');

[...document.getElementsByClassName('tom-select')].forEach(select =>
    new TomSelect(select)
);
