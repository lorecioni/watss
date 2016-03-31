close all;
load('cameraCalibration1.mat');
frame = imread('/Applications/MAMP/htdocs/watss/source/frames/1/16h21m46s_3820.jpg');

% Project point from world to image
% p = [x, y, 1]
% P = p * HW2I Projected point
% P = P ./ P(3) Normalization

mu = 0.5; 

W = I + (1/(1 - mu) - 1)*((v * l') ./ (v' * l));

%z = [768.989874403743 205.055561315589 1];
z = [569 674 1];

Z = W * z';
Z = Z ./ Z(3);


h = abs(z(2) - Z(2))
w = h/2;


frame = insertShape(frame, 'rectangle', [z(1) z(2) w h], 'LineWidth', 3);

imshow(frame);
