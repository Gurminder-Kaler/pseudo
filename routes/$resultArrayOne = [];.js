const imageArray = ['img1.jpg', 'img2.jpg', 'img3.jpg', 'img4.jpg', 'img5.jpg', 'img6.jpg', 'img7.jpg', 'img8.jpg'];

let balance = 1000;
let bet = 0;

const winningAmount = 20000;

function generateRandomNumberBetweenZeroAndEight() {
    return random(0, 7);
}

function placeBet(amount) {
    bet = amount;
}

// bet amount < balance 

function pressSpin() {

    var resultImgArray = [];
    var spinCount = 0;

    var randomNoOne = generateRandomNumberBetweenZeroAndEight(); 5
    var randomNoTwo = generateRandomNumberBetweenZeroAndEight(); 0
    var randomNoThree = generateRandomNumberBetweenZeroAndEight(); 6

    let firstImg = imageArray[randomNoOne];
    let secondImg = imageArray[randomNoTwo];
    let thirdImg = imageArray[randomNoThree];

    resultImgArray.push(firstImg);
    resultImgArray.push(secondImg);
    resultImgArray.push(thirdImg);
    if (randomNoOne ==
        randomNoTwo ==
        randomNoThree) {
        //do not minus
        balance += winningAmount;
        return "You Win";
    } else {
        balance = balance - bet;
        return "You lost";
    }

}


$('#spinBtn').on('click', function () {
    if (bet > 0 && bet <= balance) {
        pressSpin();
    }
});