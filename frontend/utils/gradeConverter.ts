export const numericToLetter = (grade: number): string => {
    if (grade > 100) grade = 100;
    if (grade < 0) grade = 0;
    
    if (grade >= 93) return 'A';
    if (grade >= 90) return 'A-';
    if (grade >= 87) return 'B+';
    if (grade >= 83) return 'B';
    if (grade >= 80) return 'B-';
    if (grade >= 77) return 'C+';
    if (grade >= 70) return 'C';
    if (grade >= 60) return 'D';
    return 'E';
};
