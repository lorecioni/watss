close all;

%load('cameraCalibration4.mat');
frame = imread('images/camera4.jpg');
HW2I=Hi2w;
imgVanline=HW2I*[0 0 1]'; % H=H(N).mat
omega=inv(K')*inv(K); %K'=ptzim(N).K'

imgVinfty=inv(omega)*imgVanline;
imgVinfty=imgVinfty/imgVinfty(3);
imgVinfty(3)=1;

p1 = [ 692  236 1];
p2 = [733 425  1];
p3 = [ 360 283 1];
p4 = [1026 174  1];

imshow(frame);
hold on;
plot(p1(1),p1(2),'r.','MarkerSize',30);
plot(p2(1),p2(2),'r.','MarkerSize',30);
plot(p3(1),p3(2),'r.','MarkerSize',30);
plot(p4(1),p4(2),'r.','MarkerSize',30);


for mu=0:-0.01:-2.0
    W=eye(3)+(1/(1-mu)-1).*((imgVinfty*imgVanline')./(imgVinfty'*imgVanline));
    hold on;
    P1 = W*[p1(1) p1(2) 1]';P1=P1./P1(3);
    plot(P1(1),P1(2),'y.','MarkerSize',30);
    P2 = W*[p2(1) p2(2) 1]';P2=P2./P2(3);
    plot(P2(1),P2(2),'y.','MarkerSize',30);
    P3 = W*[p3(1) p3(2) 1]';P3=P3./P3(3);
    plot(P3(1),P3(2),'y.','MarkerSize',30);
    P4 = W*[p4(1) p4(2) 1]';P4=P4./P4(3); 
    plot(P4(1),P4(2),'y.','MarkerSize',30);

    mu

   pause;
end
