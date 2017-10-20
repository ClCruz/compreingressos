var dataLayer = [];
var $resumoEspetaculo = $('.resumo_espetaculo');

var DataLayer = (function() {
    var product_list = [];

    function Ticket(idProduct, sellPrice, quantity) {
        this.idProduct = idProduct;
        this.sellPrice = sellPrice;
        this.quantity = quantity;
        this.type = null;
    }

    return {

        init: function($espetaculo) {
            this.$resumoEspetaculo = $espetaculo;
            this.eventoId = this.$resumoEspetaculo.data('evento');
            this.product_list = [];
            this.cacheDOM();
        },

        cacheDOM: function() {
            this.$pedidoResumo = this.$resumoEspetaculo.find('#pedido_resumo');
            this.$tiposIngressoCel = this.$pedidoResumo.find('td.tipo');
            this.$spanTotalIngresso = this.$pedidoResumo.find('span.valorIngresso');
        },

        build: function() {
            var tmpList = [],
                totalIngressos = this.$spanTotalIngresso.length,
                eventoId = this.eventoId;

            this.$tiposIngressoCel.find('option').filter(':selected').each(function(index, elem) {
                ticket = new Ticket(eventoId, $(elem).attr('valor'), 1);
                ticket.type = $(elem).text().toLowerCase();

                if(tmpList.length == 0) {
                    tmpList.push(ticket);
                } else {
                    tmpList.map(function(item, index, arr) {
                        if(item.type == ticket.type && item.idProduct == ticket.idProduct) {
                            item.quantity += 1;
                        } else if(arr.length <= totalIngressos) {
                            arr.push(ticket)
                        }
                    });
                }
            });

            product_list = product_list.concat(tmpList);
        },

        cleanUp: function() {
            product_list.map(function(item) {
                delete item.type;
            });
        },

        getProductList: function() {
            this.cleanUp();
            return product_list;
        }

    }

} ());

//        $('select[name="valorIngresso[]]"').change(function() {
//            $resumoEspetaculo.each(function() {
//                DataLayer.init($(this));
//                DataLayer.build();
//            });
//        });

$resumoEspetaculo.each(function() {
    DataLayer.init($(this));
    DataLayer.build();
});