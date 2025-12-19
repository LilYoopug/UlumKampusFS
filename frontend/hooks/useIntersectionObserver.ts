import { useState, useEffect, useRef, RefObject } from 'react';

interface IntersectionObserverOptions {
    threshold?: number | number[];
    root?: Element | null;
    rootMargin?: string;
}

export const useIntersectionObserver = (
    options: IntersectionObserverOptions
): [RefObject<HTMLDivElement>, boolean] => {
    const containerRef = useRef<HTMLDivElement>(null);
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const observer = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                setIsVisible(true);
                // No need to unobserve, let it stay visible
            }
        }, options);

        const currentRef = containerRef.current;
        if (currentRef) {
            observer.observe(currentRef);
        }

        return () => {
            if (currentRef) {
                observer.unobserve(currentRef);
            }
        };
    }, [containerRef, options]);

    return [containerRef, isVisible];
};