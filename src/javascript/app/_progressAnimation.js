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

function initImages()
{

    animationImages = [];
    x = 0;

    for (var i=0; i<frames; i++) {
        animationImages.push(new Image());
        animationImages[i].src = 'img/parrot_anime/parrot' + (i+1) + '.png';
    }

}

// Αρχίζει το progress animation
function initProgressAnimation()
{
    canvas = document.querySelector("#o-progressAnimation");
    ctx=canvas.getContext('2d');

    initImages();

    // id = requestAnimationFrame(drawImage);
    id = setInterval(drawImage,50);
}

function drawImage()
{
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.drawImage(animationImages[currentFrame], x, 0, 50, 50);
    x++;

    if(currentFrame<frames-1) {
        currentFrame++;
    } else {
        currentFrame = 0;
    }

    // id = requestAnimationFrame(drawImage);
}

