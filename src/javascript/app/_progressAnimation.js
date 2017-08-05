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
 */

var canvas, ctx, animationImages;
var x;
var frames = 6;
var currentFrame = 0;
var imageAnime, delayTheFrame;

// Αρχικοποίηση των frame του animation
function initImages()
{

    animationImages = [];
    x = 0;

    for (var i=0; i<frames; i++) {
        animationImages.push(new Image());
        animationImages[i].src = 'img/parrot_anime/parrot' + (i+1) + '_small.png';
    }

}

// Αρχίζει το progress animation
function initProgressAnimation()
{
    canvas = document.querySelector("#o-progressAnimation");
    ctx=canvas.getContext('2d');

    initImages();

    // Καθαρισμός των τρέχοντων interval
    clearInterval(delayTheFrame);
    cancelAnimationFrame(imageAnime);

    delayTheFrame = setInterval(frameDelay, 150);
    imageAnime = requestAnimationFrame(drawImage);
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

    calculateX();

    imageAnime = requestAnimationFrame(drawImage);
}

