/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.Papel;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class PapelFacade extends AbstractFacade<Papel> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public PapelFacade() {
        super(Papel.class);
    }

    @Override
    public List<Papel> findAll() {
        return getEntityManager().createNamedQuery("Papel.findAll").getResultList();
    }
    
}
