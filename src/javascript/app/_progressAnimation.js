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
 * Αρχική function που καλείται: initProgressAnimation()
 * Σταματάει το animation: killAnimation()
 *
 */

var canvas, ctx, animationImages;
var x;
var frames = 6;
var currentFrame = 0;
var imageAnimation, currentFrameInterval;
var imagePrefix1 = 'img/parrot_anime/parrot';
var imagePrefix2 = '_small.png';

// Αρχίζει το progress animation.
function initProgressAnimation(doProgress)
{
    // Δημιουργεί το o-progressAnimation μέσα στο #o-progressAnimation_container
    initCanvasElement('o-progressAnimation', '#o-progressAnimation_container');

    canvas = document.querySelector("#o-progressAnimation");
    ctx=canvas.getContext('2d');

    initImages();

    // Καθαρισμός των τρέχοντων animations
    clearAnimations();

    currentFrameInterval = setInterval(frameDelay, 150);
    imageAnimation = requestAnimationFrame(drawImage);
}

// Σταματάει κάθε animation και σβήνει το canvas element
function killProgressAnimation()
{
    clearAnimations();
    killCanvas('o-progressAnimation');
}

// Αρχικοποίηση των frame του animation
function initImages()
{
    animationImages = [];
    x = 0;

    for (var i=0; i<frames; i++) {
        animationImages.push(new Image());
        animationImages[i].src = imagePrefix1 + (i+1) + imagePrefix2;
    }
}

// Σχεδιάζει την progress bar
function drawProgressBar()
{
    ctx.fillStyle = 'white';
    ctx.fillRect(x+35, 0, canvas.width, 3);
}

// Εμφανίζει το ποσοστό
function drawProgressText()
{
    ctx.fillText(calculateProgressPercent() + '%', x, 10);
}

function calculateProgressPercent()
{
    return ( (x*100)/canvas.width ).toFixed(2);
}

// Δημιουργεί το canvas element
function initCanvasElement(elementName, canvasContainer)
{
    // Αν υπάρχει ήδη το σβήνουμε
    killCanvas(elementName);

    canvasContainerElement = document.querySelector(canvasContainer);

    canvasElement = document.createElement('canvas');
    canvasElement.setAttribute('id', elementName);
    canvasElement.setAttribute('width', canvasContainerElement.offsetWidth);
    canvasElement.setAttribute('height', canvasContainerElement.offsetHeight);

    document.querySelector(canvasContainer).appendChild(canvasElement);
}

// Καθαρισμός των τρέχοντων interval
function clearAnimations()
{
    clearInterval(currentFrameInterval);
    cancelAnimationFrame(imageAnimation);
}

// Σβήνει το elementName canvas element
function killCanvas(elementName)
{
    // Αν υπάρχει ήδη το σβήνουμε
    if($('#' + elementName).length>0) {
        $('#' + elementName).remove();
    }
}

// Υπολογίζει το τρέχον frame
function frameDelay()
{
    if(currentFrame<frames-1) {
        currentFrame++;
    } else {
        currentFrame = 0;
    }
}

// Υπολογισμός του x
function calculateX()
{
    if(x<canvas.width) {
        x++;
    } else {
        x = 0;
    }

}

// Εμφανίζει το image
function drawImage()
{
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.drawImage(animationImages[currentFrame], x, 0, 70, 70);

    // if(doProgress) { // Αν είναι true το doProgress σχεδιάζει την progress bar
    //     drawProgressBar();
    // }

    drawProgressBar();
    drawProgressText();

    calculateX();

    imageAnimation = requestAnimationFrame(drawImage);
}

