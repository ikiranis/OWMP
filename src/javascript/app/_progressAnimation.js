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
 * Αρχική function που καλείται: ProgressAnimation.init(false|true)
 * Σταματάει το animation: ProgressAnimation.kill()
 *
 */

// Εμφανίζει ένα animated sprite και αντίστοιχη progress bar
var ProgressAnimation =
{
    // Object properties

    canvas: null,                               // To canvas element
    ctx: null,                                  // Το context του canvas
    animationImages: [],                        // Τα frames του sprite
    x: 0,                                       // Η οριζόντια θέση του sprite
    progressPercent: 0,                         // Το τρέχον ποσοστό του progress
    frames: 6,                                  // Το πλήθος των frames που περιέχει το sprite
    currentFrame: 0,                            // Το τρέχον frame του sprite που εμφανίζεται
    imageAnimation: null,                       // To loop για την μετακίνηση του sprite
    currentFrameInterval: null,                 // Το loop για την εμφάνιση των frames
    imagePrefix1: 'img/parrot_anime/parrot',    // Το αρχικό κομμάτι του path για τα frames
    imagePrefix2: '_small.png',                 // Το τελικό κομμάτι του path για τα frames
    doProgress: false,                          // True για εμφάνιση progress bar, false για το αντίθετο

    // Methods

    // Αρχίζει το progress animation
    init: function(doProgress)
    {
        // Χρησιμοποιώ το bind(this) αλλιώς δεν περνάει το this στο setInterval και requestAnimationFrame
        // reference @ https://stackoverflow.com/questions/19459449/running-requestanimationframe-from-within-a-new-object
        this.drawAnimationImage = this.drawAnimationImage.bind(this);
        this.frameDelay = this.frameDelay.bind(this);

        this.doProgress = doProgress;
        this.x = 0;

        // Δημιουργεί το o-progressAnimation μέσα στο #o-progressAnimation_container
        this.initCanvasElement('o-progressAnimation', '#o-progressAnimation_container');

        // Αρχικοποίηση του canvas
        this.canvas = document.querySelector("#o-progressAnimation");
        this.ctx = this.canvas.getContext('2d');

        // Αρχικοποίηση των images για τα frames
        this.initImages();

        // Καθαρισμός των τρέχοντων animations
        this.clearAnimations();

        // Έναρξη των animation

        // Τα καρέ του animation του sprite. Χρησιμοποιείται η setInterval γιατί θέλουμε
        // πολύ μεγαλύτερη καθυστέρηση ανάμεσα στα frames
        this.currentFrameInterval = setInterval(this.frameDelay, 150);
        // Η μετακίνηση του sprite
        this.imageAnimation = requestAnimationFrame(this.drawAnimationImage);
    },

    // Σχεδιάζει το τρέχον frame
    drawAnimationImage: function()
    {
        // σβήσιμο των περιεχομένων του canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Επανασχεδίαση των περιεχομένων του canvas

        // Σχεδίαση του image
        this.ctx.drawImage(this.animationImages[this.currentFrame], this.x, 0, 70, 70);

        if(this.doProgress) { // Αν είναι true το doProgress σχεδιάζει την progress bar
            this.drawProgressBar();   // Σχεδίαση της progress bar
            this.drawProgressText();  // Σχεδίαση του κειμένου που θα εμφανιστεί
        }

        // Υπολογισμός της νέας θέσης του x
        this.calculateX();

        // Loop για το επόμενο frame
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
        this.ctx.font="20px Verdana";
        this.ctx.fillText(this.progressPercent + '%', this.x, 25);
    },

    // Επιστρέφει το ποσοστό της θέσης στην οποία βρίσκεται το sprite πάνω στο canvas
    calculateProgressPercent: function()
    {
        return ( (this.x*100)/this.canvas.width ).toFixed(0);
    },

    // θέτει το τρέχον ποσοστό του progress
    setProgressPercent: function(progressPercent)
    {
        this.progressPercent = progressPercent;
    },

    // Μετατροπή του ποσοστού progress σε x
    percentToX: function()
    {
        return ( (this.progressPercent*this.canvas.width)/100 ).toFixed(0);
    },

    // Δημιουργεί το canvas element
    initCanvasElement: function(elementName, canvasContainer)
    {
        // Αν υπάρχει ήδη το σβήνουμε
        this.killCanvas(elementName);

        canvasContainerElement = document.querySelector(canvasContainer);

        // Τα properties του canvas
        canvasElement = document.createElement('canvas');
        canvasElement.setAttribute('id', elementName);
        canvasElement.setAttribute('width', canvasContainerElement.offsetWidth);
        canvasElement.setAttribute('height', canvasContainerElement.offsetHeight);

        // Το προσθέτει μέσα στο canvas container div
        document.querySelector(canvasContainer).appendChild(canvasElement);
    },

    // Καθαρισμός των τρέχοντων animations
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
        if(this.doProgress) { // Αν εμφανίζεται η progress bar
            // TODO να μειώνεται και η ταχύτητα μετακίνησης ίσως
            if(this.x<this.percentToX()) { // Αυξάνει το this.x μέχρι το this.percentToX
                this.x++;
            }
        } else {
            if(this.x<this.canvas.width) {
                this.x++;
            } else {
                this.x = 0;
            }
        }

    }

}
