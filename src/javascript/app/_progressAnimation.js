/**
 *
 * File: _progressAnimation.js
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 * Date: 03/08/17
 * Time: 01:40
 *
 * Η διαχείριση του progress animation
 *
 * Αρχική function που καλείται: init()
 * Σταματάει το animation: kill()
 *
 */

var ProgressAnimation =
{
    canvas: null,
    ctx: null,
    animationImages: [],
    x: 0,
    frames: 6,
    currentFrame: 0,
    imageAnimation: null,
    currentFrameInterval: null,
    imagePrefix1: 'img/parrot_anime/parrot',
    imagePrefix2: '_small.png',

    // Αρχίζει το progress animation
    init: function(doProgress)
    {
        // Χρησιμοποιώ το bind(this) αλλιώς δεν παιρνάει το this στο setInterval και requestAnimationFrame
        this.drawAnimationImage = this.drawAnimationImage.bind(this);
        this.frameDelay = this.frameDelay.bind(this);

        // Δημιουργεί το o-progressAnimation μέσα στο #o-progressAnimation_container
        this.initCanvasElement('o-progressAnimation', '#o-progressAnimation_container');

        this.canvas = document.querySelector("#o-progressAnimation");
        this.ctx = this.canvas.getContext('2d');

        this.initImages();

        // Καθαρισμός των τρέχοντων animations
        this.clearAnimations();

        this.currentFrameInterval = setInterval(this.frameDelay, 150);
        this.imageAnimation = requestAnimationFrame(this.drawAnimationImage);
    },

    // Σταματάει κάθε animation και σβήνει το canvas element
    kill: function()
    {
        this.clearAnimations();
        this.killCanvas('o-progressAnimation');
    },

    // Αρχικοποίηση των frames του animation
    initImages: function()
    {
        this.animationImages = [];

        for (var i=0; i<this.frames; i++) {
            this.animationImages.push(new Image());
            this.animationImages[i].src = this.imagePrefix1 + (i+1) + this.imagePrefix2;
        }
    },

    // Σχεδιάζει την progress bar
    drawProgressBar: function()
    {
        this.ctx.fillStyle = 'white';
        this.ctx.fillRect(this.x+35, 0, this.canvas.width, 3);
    },

    // Εμφανίζει το ποσοστό
    drawProgressText: function()
    {
        this.ctx.fillText(this.calculateProgressPercent() + '%', this.x, 10);
    },

    calculateProgressPercent: function()
    {
        return ( (this.x*100)/this.canvas.width ).toFixed(2);
    },

    // Δημιουργεί το canvas element
    initCanvasElement: function(elementName, canvasContainer)
    {
        // Αν υπάρχει ήδη το σβήνουμε
        this.killCanvas(elementName);

        canvasContainerElement = document.querySelector(canvasContainer);

        canvasElement = document.createElement('canvas');
        canvasElement.setAttribute('id', elementName);
        canvasElement.setAttribute('width', canvasContainerElement.offsetWidth);
        canvasElement.setAttribute('height', canvasContainerElement.offsetHeight);

        document.querySelector(canvasContainer).appendChild(canvasElement);
    },

    // Καθαρισμός των τρέχοντων interval
    clearAnimations: function()
    {
        clearInterval(this.currentFrameInterval);
        cancelAnimationFrame(this.imageAnimation);
    },

    // Σβήνει το elementName canvas element
    killCanvas: function(elementName)
    {
        // Αν υπάρχει ήδη το σβήνουμε
        if($('#' + elementName).length>0) {
            $('#' + elementName).remove();
        }
    },

    // Υπολογίζει το τρέχον frame
    frameDelay: function()
    {
        if(this.currentFrame<this.frames-1) {
            this.currentFrame++;
        } else {
            this.currentFrame = 0;
        }
    },

    // Υπολογισμός του x
    calculateX: function()
    {
        if(this.x<this.canvas.width) {
            this.x++;
        } else {
            this.x = 0;
        }
    },

    // Σχεδιάζει το τρέχον frame
    drawAnimationImage: function()
    {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.ctx.drawImage(this.animationImages[this.currentFrame], this.x, 0, 70, 70);

        // if(doProgress) { // Αν είναι true το doProgress σχεδιάζει την progress bar
        //     drawProgressBar();
        // }

        this.drawProgressBar();
        this.drawProgressText();

        this.calculateX();

        this.imageAnimation = requestAnimationFrame(this.drawAnimationImage);
    }

}
