<?php
    require __DIR__ . "/model.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" type="text/css" href="css/all.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
</head>
<body>
    <main id="main">
        <div id="content" class="container">
        <?php foreach (getItems(1, 4) as $item): ?>
            <div class="item">
                <img src="<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>">
                <h2 class="item-title"><?php echo $item['title']; ?></h2>
                <p class="item-description"><?php echo $item['description']; ?></p>
                <div class="row-container cost-section">
                    <span class="item-cost">
                       $ <?php echo $item['discountCost'] ? $item['discountCost'] : $item['cost']; ?>
                    </span>

                    <?php if ($item['discountCost'] !== null): ?>
                    <span class="discount-cost">
                       $ <?php echo $item['cost']; ?>
                    </span>
                    <span class="discount-lable">
                        Sale
                    </span>
                <?php endif; ?>
                </div>
                <?php if ($item['new']): ?>
                    <span class="new-lable">
                    New
                    </span>
                <?php endif; ?>
                <div class="row-container button-section">
                    <a class="btn add" href="#">Add to cart</a>
                    <a class="btn view" href="#">View </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="button-holder">
        <div id="preloader" class="preloader"></div>
        <button class="btn" id="button">Load more</button>
    </div>
    </main>
    
    
    <script type="text/javascript">
        $(document).ready(function(){
            const content = $("#content");
            const button = $('#button');
            const preloader = $('#preloader');

            class Listener{
                constructor (listener, handler) {
                    this.listener = listener;
                    this.handler = handler;
                }
                stateWasChange(state) {
                    this.handler(this.listener, state);
                }
            }

            class State {
                constructor () {
                    this._preloader = false;
                    this._page = 1;
                    this.stateReady = true;
                    this.tmp = '';
                    this.total = 10;
                    this.stateChangeListeners = [];
                    this.counterListeners = [];
                }
                get preloader(){
                    return this._preloader;
                }
                set preloader(bool){
                    if (typeof bool == "boolean"){
                        
                        for (let i = this.stateChangeListeners.length - 1; i >= 0; i--) {
                            this.stateChangeListeners[i].stateWasChange(bool);
                        }
                        return this._preloader = bool;
                    } else{
                        return console.log('Type of value was not boolean');
                    }
                }
                get page(){
                    return this._page;
                }
                set page(value){
                    if (typeof value == "number"){
                        if(this.total < (this._page * 4)){
                            for (let i = this.counterListeners.length - 1; i >= 0; i--) {
                                this.counterListeners[i].stateWasChange();
                            }  
                        }
                        return this._page = value;
                    } else{
                        return console.log('Type of value was not number');
                    }
                }
                getData(page){
                    return $.ajax({url: "list.php", data:{page: page, per_page: 4}});
                }
                tmpGenerator(data){
                    let source = JSON.parse(data);
                    let template = '';
                    for (let item in source.entities) {
                        template += `
                        <div class="item"><img src=${source.entities[item].img} alt=${source.entities[item].title}>
                             <h2 class="item-title">${source.entities[item].title}</h2>
                            <p class="item-description">${source.entities[item].description}</p>
                            <div class="row-container cost-section">
                                ${source.entities[item].discountCost ? `<span class="item-cost">$ ${source.entities[item].discountCost}</span>` :
                                `<span class="item-cost">$ ${source.entities[item].cost}</span>`}
                                ${source.entities[item].discountCost ? `<span class="discount-cost">$ ${source.entities[item].cost}</span> 
                                <span class="discount-lable">Sale</span>` : ``}
                                 ${source.entities[item].new ? `<span class="new-lable">New</span>`:``}
                            </div>
                            <div class="row-container button-section">
                                <a class="btn add" href="#">Add to cart</a>
                                <a class="btn view" href="#">View </a>
                            </div>
                        </div>`;
                    }
                    return template;
                }
                append (elem, template) {
                    console.log('append')
                    elem.append($(template).fadeIn('slow'));
                }
                update(elem){
                    let self = this;
                    elem.click(function(){
                        if(self.tmp !== ''){
                            self.append (content, self.tmp);
                            self.tmp = '';
                        }
                        if (!self.stateReady) self.preloader = true;
                        if(self.stateReady){
                            self.stateReady = false;
                            self.getData(self.page + 1, 4)
                            .done(function(data) {
                                self.tmp = self.tmpGenerator(data);
                                self.stateReady = true;
                                self.preloader = false;
                                self.page = ++self.page;
                            })
                            .fail(function(xhr) {
                                console.log('Wooops, somthing went wrong', xhr)
                            });
                        }
                    })
                }
                start(button, listener){
                    this.update(button);
                    button.click()
                    this.counterListeners.push(new Listener(button, function(listener){
                        button.addClass('hidden');
                    }));
                    this.stateChangeListeners.push(new Listener(listener, function(listener, state){
                        state ? listener.addClass('visible') : listener.removeClass('visible');
                    }));
                }
            }
            function init(){
                let state = new State();
                state.start(button, preloader);
            }
            init();
        });
    </script>
</body>
</html>